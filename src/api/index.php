<?php
$_SERVER["REQUEST_URI"] = str_replace("/api/", "/", $_SERVER["REQUEST_URI"]);
require './vendor/autoload.php';

use Phalcon\Mvc\Micro;
use Phalcon\Loader;
use Phalcon\Di\FactoryDefault;
use Phalcon\Events\Manager as EventsManager;

//---------------------------------------------------------Loader------------------------------------------
$loader = new Loader();
$loader->registerNamespaces(
    [
        'Api\Components' => './components',
        'Api\Handlers' => './handlers'
    ]
);

$loader->register();
//---------------------------------------------------------Loader End------------------------------------------


$container = new FactoryDefault();
$app =  new Micro($container);
//---------------------------------------------------------DataBase------------------------------------------

$container->set(
    'mongo',
    function () {
        $mongo = new \MongoDB\Client(
            "mongodb://mongo",
            array(
                "username" => 'root',
                "password" => "password123"
            )
        );
        return $mongo->store;
    },
    true
);
//---------------------------------------------------------DataBase End------------------------------------------


$prod = new Api\Handlers\Product();
$order = new Api\Handlers\Order();

//----------------------------------------------------Validate User -----------------------------------------
$eventsManager = new EventsManager();
$eventsManager->attach(
    'micro',
    new Api\Components\MiddleWare()
);
$app->before(
    new Api\Components\MiddleWare()

);
//----------------------------------------------------Validating User End-----------------------------------------


$app->get(
    '/products/allProducts',
    [
        $prod,
        'allProducts'
    ]
);



$app->get(
    '/products/createToken',
    [
        $prod,
        'createToken'
    ]
);

$app->post(
    '/orders/create',
    [
        $order,
        'create'
    ]
);

$app->put(
    '/orders/update',
    [
        $order,
        'update'
    ]
);

$app->setEventsManager($eventsManager);
$app->handle(
    $_SERVER["REQUEST_URI"]
);
