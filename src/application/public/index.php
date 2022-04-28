<?php
$_SERVER['REQUEST_URI'] = str_replace('/application/', '/', $_SERVER['REQUEST_URI']);

use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Url;
use Phalcon\Session\Manager;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Events\Manager as EventsManager;

session_start();
require_once '../vendor/autoload.php';
// Define some absolute path constants to aid in locating resources
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('BASE_URI', 'http://'.$_SERVER['HTTP_HOST'].'/application/');

// Register an autoloader
$loader = new Loader();

$loader->registerDirs(
    [
        APP_PATH . '/controllers/',
        APP_PATH . '/models/',
    ]
);

$loader->register();
$loader->registerNamespaces(
    [
        'App\Components' => APP_PATH . '/components',
    ]
);

$container = new FactoryDefault();
$application = new Application($container);
$container->set(
    'view',
    function () {
        $view = new View();
        $view->setViewsDir(APP_PATH . '/views/');
        return $view;
    }
);

$container->set(
    'event',
    function () {
        $eventsManager = new EventsManager();
        $eventsManager->attach(
            'notifications',
            new App\Components\NotificationsListener()
        );
        return $eventsManager;
    }
);

$container->set(
    'url',
    function () {
        $url = new Url();
        $url->setBaseUri('/');
        return $url;
    }
);

// $container->set(
//     'db',
//     function () {
//         return new Mysql(
//             [
//                 'host'     => 'mysql-server',
//                 'username' => 'root',
//                 'password' => 'secret',
//                 'dbname'   => 'store',
//             ]
//         );
//     }
// );

$container->set(
    'session',
    function () {
        $session = new Manager();
        $files = new Stream(
            [
                'savePath' => '/tmp',
            ]
        );
        $session
            ->setAdapter($files)
            ->start();

        return $session;
    },
    true
);

$container->set(
    'mongo',
    function () {
        $mongo = new \MongoDB\Client('mongodb://mongo',
        ['username' => 'root', 'password' => 'password123']
    );
        return $mongo->store;
    },
    true
);

try {
    // Handle the request
    $response = $application->handle(
        $_SERVER['REQUEST_URI']
    );

    $response->send();
} catch (\Exception $event) {
    echo 'Exception: ', $event->getMessage();
}
