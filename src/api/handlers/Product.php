<?php

namespace Api\Handlers;

use Phalcon\Di\Injectable;

/**
 * Producr Controller
 * to handle all the product requests
 */     
class Product extends Injectable
{

    function createToken()
    { 
    }
    /**
     * allProducts Action
     * To get all the products
     * @return void
     */
    function allProducts()
    {
        $result = $this->mongo->product->find();
        $this->response->setStatusCode(200, 'Found');
        $this->response->setJsonContent([
            "status" => "200",
            "data" => $result->toArray()
        ]);
        $this->response->send();
    }
}
