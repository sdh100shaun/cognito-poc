<?php
declare(strict_types=1);

namespace App\Services;

use App\Configuration;
use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Aws\ResultInterface;
use Exception;

class CognitoIdentityProvider
{


    /**
     * @var CognitoIdentityProviderClient
     */
    private $client;
    /**
     * @var Configuration
     */
    private $configuration;

    private $user = null;


    public function __construct(CognitoIdentityProviderClient $client, Configuration $configuration)
    {
        $this->configuration = $configuration;
        $this->client = $client;

    }

    public function initialise($accessToken = null): void
    {
        try {
            $this->user = $this->client->getUser([
                'AccessToken' => $accessToken
            ]);
        } catch (\Exception  $e) {
            // an exception indicates the accesstoken is incorrect - $this->user will still be null

        }
    }

    public function authenticate(string $username, string $password): ResultInterface
    {
        try {
            $result = $this->client->adminInitiateAuth([
                'AuthFlow' => 'ADMIN_NO_SRP_AUTH',
                'ClientId' => $this->configuration->getClientId(),
                'UserPoolId' => $this->configuration->getUserpoolId(),
                'AuthParameters' => [
                    'USERNAME' => $username,
                    'PASSWORD' => $password,
                ],
            ]);
        } catch (\Exception $e) {
            return (new Result(['error' => $e->getMessage()]));
        }
        return $result;
    }

    public function replaceTemporaryPassword(
        string $username,
        string $newPassword
    ): ResultInterface {
         $result = $this->client->respondToAuthChallengeAsync([
            'ClientId' => $this->configuration->getClientId(),
            'UserPoolId' => $this->configuration->getUserpoolId(),
            'Session' => $this->getAuthenticationCookie(),
            'ChallengeResponses' => [
                'NEW_PASSWORD' => $newPassword,
                'USERNAME' => $username
            ],
            'ChallengeName' => 'NEW_PASSWORD_REQUIRED'
        ])->wait();

         return  $result;
    }


    public function signup(string $username, string $email, string $password): ResultInterface
    {
        try {
            $result = $this->client->signUp([
                'ClientId' => $this->configuration->getClientId(),
                'Username' => $username,
                'Password' => $password,
                'UserAttributes' => [
                    [
                        'Name' => 'name',
                        'Value' => $username
                    ],
                    [
                        'Name' => 'email',
                        'Value' => $email
                    ]
                ],
            ]);
        } catch (\Exception $e) {
            return (new Result(['error' => $e->getMessage()]));
        }

        return $result;
    }

    public function confirmSignup(string $username, string $code): ResultInterface
    {
        try {
            $result = $this->client->confirmSignUp([
                'ClientId' => $this->configuration->getClientId(),
                'Username' => $username,
                'ConfirmationCode' => $code,
            ]);
        } catch (\Exception $e) {
            return (new Result(['error' => $e->getMessage()]));
        }

        return $result;
    }

    public function sendPasswordResetMail(string $username): string
    {
        try {
            $this->client->forgotPassword([
                'ClientId' => $this->configuration->getClientId(),
                'Username' => $username
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return '';
    }

    public function resetPassword(string $code, string $password, string $username): string
    {
        try {
            $this->client->confirmForgotPassword([
                'ClientId' => $this->configuration->getClientId(),
                'ConfirmationCode' => $code,
                'Password' => $password,
                'Username' => $username
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return '';
    }

    public function isAuthenticated(): bool
    {
        return null !== $this->user;
    }

    public function getPoolMetadata(): array
    {
        $result = $this->client->describeUserPool([
            'UserPoolId' => $this->configuration->getUserpoolId(),
        ]);

        return $result->get('UserPool');
    }

    public function getPoolUsers(): array
    {
        $result = $this->client->listUsers([
            'UserPoolId' => $this->configuration->getUserpoolId(),
        ]);

        return $result->get('Users');
    }

    public function getUser(): ?\Aws\Result
    {
        return $this->user;
    }

    public function logout()
    {
        if (isset($_COOKIE[self::COOKIE_NAME])) {
            unset($_COOKIE[self::COOKIE_NAME]);
            setcookie(self::COOKIE_NAME, '', time() - 3600);
        }
    }

    /**
     * Create Cognito secret hash
     *
     * @param string $username
     * @param string $secret
     *
     * @return string
     */
    protected function cognitoSecretHash(string $username, string $secret)
    {
        return $this->hash($username . $this->configuration->getClientId(), $secret);
    }

    /**
     * Create HMAC from string
     *
     * @param  string $message
     * @return string
     */
    protected function hash($message,$secret)
    {
        $hash = hash_hmac(
            'sha256',
            $message,
            $secret,
            true
        );

        return base64_encode($hash);
    }
}