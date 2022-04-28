<?php

use Phalcon\Mvc\Controller;
use MongoDB\BSON\ObjectID;

class IndexController extends Controller
{

  public function indexAction()
  {
    $this->view->products = $this->mongo->product->find()->toArray();
  }

  public function deleteAction()
  {
    $data = $this->request->getPost("data");
    $this->mongo->product->deleteOne(
      ["_id" => new ObjectID($data)]
    );
  }

  public function createAction()
  {
    $data = json_decode($this->request->getPost("data"), true);
    $this->mongo->product->insertOne(
      ["_id" => new ObjectID($data['_id']['$oid']), "name" => $data['name'], "price" => $data["price"], "stock" => $data['stock']]
    );
  }

  public function updateAction()
  {
    $data = json_decode($this->request->getPost("data"), true);
    foreach ($data as $key => $value) {
      $this->mongo->product->updateOne(
        ["_id" => new ObjectID($value['_id']['$oid'])],
        ['$set' => ["name" => $value['name'], "price" => $value["price"], "stock" => $value['stock']]]
      );
    }
  }
}
