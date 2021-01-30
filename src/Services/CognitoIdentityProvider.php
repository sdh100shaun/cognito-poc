<?php
declare(strict_types=1);

namespace App\Services;

use App\Configuration;
use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Aws\ResultInterface;
use Exception;


class CognitoIdentityProvider
{

    private const COOKIE_NAME = 'aws-cognito-app-access-token';

    /**
     * @var CognitoIdentityProviderClient
     */
    private $client;
    /**
     * @var Configuration
     */
    private $configuration;

    private $user = null;


    public function __construct(Configuration $configuration)
    {


        $this->configuration = $configuration;
    }

    public function initialize() : void
    {
        $this->client = new CognitoIdentityProviderClient([
            'version' => '2016-04-18',
            'region' => $this->configuration->getRegion(),
        ]);

        try {
            $this->user = $this->client->getUser([
                'AccessToken' => $this->getAuthenticationCookie()
            ]);
        } catch(\Exception  $e) {
            // an exception indicates the accesstoken is incorrect - $this->user will still be null
        }
    }

    public function authenticate(string $username, string $password) : ResultInterface
    {
        try {
            $result = $this->client->adminInitiateAuth([
                'AuthFlow' => 'ADMIN_NO_SRP_AUTH',
                'ClientId' => $this->configuration->getClientId(),
                'UserPoolId' => $this->configuration->getClientId(),
                'AuthParameters' => [
                    'USERNAME' => $username,
                    'PASSWORD' => $password,
                ],
            ]);
        } catch (\Exception $e) {
            return (new Result(['error'=> $e->getMessage()]));
        }

        $this->setAuthenticationCookie($result->get('AuthenticationResult')['AccessToken']);

        return $result;
    }

    public function signup(string $username, string $email, string $password) : ResultInterface
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
            return (new Result(['error'=> $e->getMessage()]));
        }

        return $result;
    }

    public function confirmSignup(string $username, string $code) : ResultInterface
    {
        try {
            $result = $this->client->confirmSignUp([
                'ClientId' => $this->configuration->getClientId(),
                'Username' => $username,
                'ConfirmationCode' => $code,
            ]);
        } catch (\Exception $e) {
          return (new Result(['error'=> $e->getMessage()]));
        }

        return $result;
    }

    public function sendPasswordResetMail(string $username) : string
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

    public function resetPassword(string $code, string $password, string $username) : string
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

    public function isAuthenticated() : bool
    {
        return null !== $this->user;
    }

    public function getPoolMetadata() : array
    {
        $result = $this->client->describeUserPool([
            'UserPoolId' => $this->configuration->getUserpoolId(),
        ]);

        return $result->get('UserPool');
    }

    public function getPoolUsers() : array
    {
        $result = $this->client->listUsers([
            'UserPoolId' => $this->configuration->getUserpoolId(),
        ]);

        return $result->get('Users');
    }

    public function getUser() : ?\Aws\Result
    {
        return $this->user;
    }

    public function logout()
    {
        if(isset($_COOKIE[self::COOKIE_NAME])) {
            unset($_COOKIE[self::COOKIE_NAME]);
            setcookie(self::COOKIE_NAME, '', time() - 3600);
        }
    }

    private function setAuthenticationCookie(string $accessToken) : void
    {
        /**
         * not secure way of stiring for demo purposes only
         */
        setcookie(self::COOKIE_NAME, $accessToken, time() + 3600);
    }

    private function getAuthenticationCookie() : string
    {
        return $_COOKIE[self::COOKIE_NAME] ?? '';
    }
}