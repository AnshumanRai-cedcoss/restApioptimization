<?php

namespace Api\Handlers;

use Phalcon\Di\Injectable;
use MongoDB\BSON\ObjectID;

/**
 * Order Controller
 * to handle all the order requests
 */
class Order extends Injectable
{
    /**
     *Create function
     *To place an order 
     */
    function create()
    {
        $getproduct = json_decode(json_encode($this->request->getJsonRawBody()), true);
        $result = $this->mongo->orders->insertOne([
            "email" => $GLOBALS['userMail'],
            "name" => $GLOBALS['userNm'],
            "product_id" => $getproduct['product_id'],
            "quantity" => $getproduct['quantity'],
            'date' => date('d-m-Y'),
            "status" => 'Paid'
        ]);

        $res = " Order id:" . $result->getInsertedId() . "";

        $this->response->setStatusCode(200, 'Found');
        $this->response->setJsonContent([
            "status" => "Order Placed Successfully!!",
            "data" => $res
        ]);
        $this->response->send();
    }

    /**
     * Update function
     * To update an order by API
     * @return void
     */
    function update()
    {
        $getproduct = json_decode(json_encode($this->request->getJsonRawBody()), true);
        $this->mongo->orders->updateOne(
            ["_id" => new ObjectID($getproduct['order_id'])],
            ['$set' => ["status" => ($getproduct['status'])]]
        );
        $result = $getproduct['status'];
        $this->response->setStatusCode(200, 'Order Updated');
        $this->response->setJsonContent([
            "status" => "Order Updated Successfully!!",
            "status_value" => $result
        ]);
        $this->response->send();
    }
}
