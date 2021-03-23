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
            return $view->render($response, 'replace.twig.html', ['username'=>$args['username']
            ]);
        }
        else if(!isset($result['error'])){
            $session = $this->get('session');
            /**
             *var /SlimSession/SessionHelper
             */
           $session->set('cog-result', $result);
           $url = '/secure';
           return $response->withHeader('Location',$url);
        }
        return $view->render($response, 'index.twig.html', ['error'=>$result['error']]);
    });

    $app->post('/replace', function (Request $request, Response $response) use ($view) {
        /**
         * @var \App\Services\CognitoIdentityProvider
         */
        $identity = $this->get('identity');
        $identity->initialise();
        $args = $request->getParsedBody();
        /**
         * @var \Aws\ResultInterface
         */
        $previousPassword = $args['password'];
        if($args['newPassword'] === $args['confirmPassword'] ){
            $newPassword = $args['newPassword'];
        }
        else {
            return "passwords don't match";
        }
        $username = $args['username'];
        $result = $identity->replaceTemporaryPassword(
            $username,
            $newPassword
        );


        $url = '/secure';
        return $response->withHeader('Location',$url);

    });

    $app->get('/secure',function (Request $request, Response $response) use ($view) {
        $session = $this->get('session');
        if($session->exists('cog-result')){
            return $view->render($response, 'secure.twig.html', ['value'=>'secure','session'=>$session->get('cog-result')['AuthenticationResult']]);
        }else
        {
            $url = '/';
            return $response->withHeader('Location',$url);
        }

    });


};
