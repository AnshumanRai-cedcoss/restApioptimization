<?php

use Phalcon\Mvc\Controller;
use MongoDB\BSON\ObjectID;

/**
 * Admin Controller
 * All the work of the admin
 */
final class AdminController extends Controller
{
  /**
   * To view all orders for admin
   */
  public function indexAction(): void
  {
      $object = new App\Components\Helper();
      $object->validateAdmin();
      $this->view->orders = $this->mongo->orders->find()->toArray();
  }

  /**
   * addProduct function
   * Admin adding a product
   */
  public function addProductAction(): void
  {
      $object = new App\Components\Helper();
      $object->validateAdmin();
      if ($this->request->has('add')) {
        $data = $this->request->getPost();
        $this->mongo->product->insertOne([
            'name' => $data["name"],
            'price' => $data["price"],
            'stock' => $data["stock"]
        ]);
        $this->event->fire('notifications:check', $this, "create");
        $this->response->redirect(BASE_URI . 'admin/productList');
      }
  }

  /**
   * ProductList function
   * To list all products
   */
  public function productListAction(): void
  {
      $object = new App\Components\Helper();
      $object->validateAdmin();
      $this->view->products = $this->mongo->product->find()->toArray();
  }

  /**
   * deleting the product by admin
   * product id is must
   */
  public function deleteProdAction(): void
  {
      $object = new App\Components\Helper();
      $object->validateAdmin();
      $id = $this->request->get("id");
      $this->mongo->product->deleteOne(
        ["_id" => new ObjectID($id)]
      );
      $this->event->fire('notifications:check', $this, "delete");
      $this->response->redirect(BASE_URI . 'admin/productList');
  }

  /**
   * Change Status
   * When admin changes the status
   */
  public function statusChangeAction(): void
  {
      $object = new App\Components\Helper();
      $object->validateAdmin();
      $data = $this->request->getPost();
      $this->mongo->orders->updateOne(
        ["_id" => new ObjectID($data['id'])],
        ['$set' => ["status" => $data['status']]]
      );
      $this->response->redirect(BASE_URI . 'admin');
  }

  /**
   * updateProduct Action
   * When admin updates a product
   */
  public function updateProdAction(): void
  {
      $object = new App\Components\Helper();
      $object->validateAdmin();
      $id = $this->request->get("id");
      $result = $this->mongo->product->findOne(['_id' => new ObjectID($id)]);
      $this->view->data = (array) $result;
      if ($this->request->has('update')) {
        $res = $this->request->getPost();
        $this->mongo->product->updateOne(
          ["_id" => new ObjectID($id)],
          ['$set' => ["name" => $res['name'], "price" => $res["price"], "stock" => $res['stock']]]
        );
        $this->event->fire('notifications:check', $this, "update");
        $this->response->redirect(BASE_URI . 'admin/productList');
      }
  }
}
