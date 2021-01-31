<?php


namespace Tests;


use App\Configuration;
use App\Services\CognitoIdentityProvider;
use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Mockery;

class CognitoProviderTest extends TestCase
{

    /**
     * @var CognitoIdentityProvider
     */
    private $sut;
    /**
     * @var Configuration|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $mockConfiguration;
    /**
     * @var CognitoIdentityProviderClient|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $mockClient;

    public function setUp(): void
    {
        $this->mockConfiguration = Mockery::mock(Configuration::class);
        $this->mockClient = Mockery::mock(CognitoIdentityProviderClient::class);
        $this->sut = new CognitoIdentityProvider($this->mockClient,$this->mockConfiguration);
    }

    public function testUserNullIfNotIntialised()
    {
        $this->assertNull($this->sut->getUser(),'User should be null');
    }
}