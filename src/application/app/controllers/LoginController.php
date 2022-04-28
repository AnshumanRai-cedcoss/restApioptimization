<?php

use Phalcon\Mvc\Controller;

final class LoginController extends Controller
{
  public function indexAction(): void
  {
    if ($this->request->has('loginAdmin')) {
      $data = $this->request->getPost();
      $userInfo = $this->mongo->users->findOne([
          'email' => $data['email'],
          'password' => $data['password']
      ]);
      /**
       * Matching Credentials
       */
      if (isset($userInfo)) {
        $this->response->redirect(BASE_URI . $userInfo->role);
        $this->session->set('user', ['role' => $userInfo->role, 'email' => $userInfo->email]);//setting session
      } else {
        $this->view->message = 'Wrong Credentials';
      }
    }
  }
}
