<?php
declare(strict_types=1);

use App\Application\Application;
use App\Configuration;
use App\Services\CognitoIdentityProvider;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

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
        $container->get('configuration')
    );
});
// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();
$callableResolver = $app->getCallableResolver();

// Add Twig-View Middleware
$app->add(TwigMiddleware::createFromContainer($app));
$app->addBodyParsingMiddleware();
// Register routes
$routes = require __DIR__ . '/../app/routes.php';
$routes($app);

$app->run();