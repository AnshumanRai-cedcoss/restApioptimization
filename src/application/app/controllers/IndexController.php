<?php

use GuzzleHttp\Client;
use Phalcon\Mvc\Controller;

final class IndexController extends Controller
{
    public function indexAction(): void
    {
    }

    public function placeOrderAction(): void
    {
      $this->view->data = $this->mongo->product->find()->toArray();
      if ($this->request->has('placeOrder')) {
        $data = $this->request->getPost();
        $res = $this->mongo->users->findOne(["email" => $data['email']]);
        if (isset($res)) {
          $token = $res->token;
          $result = $this->mongo->product->findOne(["_id" => new MongoDB\BSON\ObjectID($data["product"])]);
          if ($result->stock < $data["quantity"])
          {
            $this->view->message = "Product out of stock!Please try again later or lessen the quantity";
          }
          else
          {
            $url = "http://192.168.2.38:8080/api/orders/create?token={$token}";
            $body = [
                "product_id" => $data["product"],
                "quantity" => $data["quantity"]
            ];
            $client = new Client();
            $result = $client->request(
              'POST',
              $url,
              ["body" => json_encode($body)]
            );
            $res = json_decode($result->getBody(), true);
            $this->view->success = $res;
          }
        } else {
          $this->view->message = "Not a valid email";
        }
      }
    }
}
