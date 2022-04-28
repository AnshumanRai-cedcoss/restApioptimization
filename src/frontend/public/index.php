<?php
$_SERVER["REQUEST_URI"] = str_replace("/frontend/", "/", $_SERVER["REQUEST_URI"]);
use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Url;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Config;
use Phalcon\Session\Manager;
use \Phalcon\Debug;
use Phalcon\Session\Adapter\Stream;

session_start();
$config = new Config([]);
require_once('../vendor/autoload.php');
// Define some absolute path constants to aid in locating resources
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('BASE_URI',$_SERVER["HTTP_HOST"]);


// Register an autoloader
$loader = new Loader();

$loader->registerDirs(
    [
        APP_PATH . "/controllers/",
        APP_PATH . "/models/",
    ]
);

$loader->register();
$loader->registerNamespaces(
    [
        'App\Components' => APP_PATH . '/components'
    ]
);

$container = new FactoryDefault();

$container->set(
    'view',
    function () {
        $view = new View();
        $view->setViewsDir(APP_PATH . '/views/');
        return $view;
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

$application = new Application($container);



$container->set(
    'db',
    function () {
        return new Mysql(
            [
                'host'     => 'mysql-server',
                'username' => 'root',
                'password' => 'secret',
                'dbname'   => 'store',
            ]
        );
    }
);

$debug = new Debug();
$debug->listen();
$profiler = new \Fabfuel\Prophiler\Profiler();
$toolbar = new \Fabfuel\Prophiler\Toolbar($profiler);
$toolbar->addDataCollector(new \Fabfuel\Prophiler\DataCollector\Request());
echo $toolbar->render();

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
        $mongo = new \MongoDB\Client("mongodb://mongo", array("username" => 'root', "password" => "password123"));

        return $mongo->frontEnd;
    },
    true
);

try {
    // Handle the request
    $response = $application->handle(
        $_SERVER["REQUEST_URI"]
    );

    $response->send();
} catch (\Exception $e) {
    echo 'Exception: ', $e->getMessage();
}
