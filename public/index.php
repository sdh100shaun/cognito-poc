<?php
declare(strict_types=1);

use App\Application\Application;
use App\Configuration;
use App\Services\CognitoIdentityProvider;
use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Slim\Factory\AppFactory;
use Slim\Middleware\Session;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use SlimSession\Helper;

require __DIR__ . '/../vendor/autoload.php';
$containerBuilder = new ContainerBuilder();

// Build PHP-DI Container instance
$container = $containerBuilder->build();
// Set view in Container
$container->set('view', function() {
    return Twig::create(__DIR__ . '/../templates', ['cache' => __DIR__ . '/../var/cache']);
});

$container->set('configuration', function (){
   $dotenv = Dotenv::createMutable(__DIR__."/../");
   return new Configuration($dotenv);
});

$container->set('identity', function() use ($container) {
    return new  CognitoIdentityProvider(
        $container->get('cognitoClient'),
        $container->get('configuration')
    );
});

$container->set('cognitoClient', function() use ($container) {
    $configuration = $container->get('configuration');
    return new CognitoIdentityProviderClient(
        [
            'profile' => $configuration->getProfile(),
            'region' => $configuration->getRegion(),
            'version' => 'latest'
        ]
    );
});

// Register globally to app
$container->set('session', function () {
    return new Helper();
});
// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();
$callableResolver = $app->getCallableResolver();
$app->add(
    new Session([
        'name' => 'session-cog',
        'autorefresh' => true,
        'lifetime' => '1 hour',
    ])
);
// Add Twig-View Middleware
$app->add(TwigMiddleware::createFromContainer($app));
$app->addBodyParsingMiddleware();
// Register routes
$routes = require __DIR__ . '/../app/routes.php';
$routes($app);

$app->run();