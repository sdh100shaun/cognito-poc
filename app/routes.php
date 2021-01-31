<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;


return function (App $app) {
    $view = $app->getContainer()->get('view');
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) use ($view) {
        return $view->render($response, 'index.twig.html', [
        ]);
    });


    $app->post('/login', function (Request $request, Response $response) use ($view) {
        /**
         * @var \App\Services\CognitoIdentityProvider
         */
        $identity = $this->get('identity');
        $identity->initialise();
        $args = $request->getParsedBody();
        /**
         * @var \Aws\ResultInterface
         */
        $result = $identity->authenticate($args['username'], $args['password']);

        if ($result->get('ChallengeName') === 'NEW_PASSWORD_REQUIRED') {
            return $view->render($response, 'replace.twig.html', [
            ]);
        }
        else {
            $url = '/secure';
            $response->withHeader('Location:'.$url);
        }

    });


};
