<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Phalcon\Mvc\Controller;

final class UserController extends Controller
{
    public function indexAction(): void
    {
        if ($this->request->has('addUser')) {
            $data = $this->request->getPost();
            $result = $this->mongo->users->findOne(['email' => $data['email']]);
            if (count($result) <= 0) {
                $obj = new \App\Components\Helper();
                $jwt = $obj->tokenValidate($data['uName'], $data['email']);
                $this->mongo->users->insertOne([
                    'name' => $data['uName'],
                    'email' => $data['email'],
                    'password' => $data['password'],
                    'role' => 'user/webhook',
                    'token' => $jwt,
                ]);
                $this->view->token = $jwt;
            } else {
                $this->view->message = 'Email already exists!Please sign in';
            }
        }
    }

    /**
     * Webhook function
     */
    public function webhookAction(): void
    {
        $object = new App\Components\Helper();
        $object->validate();
        $result = $this->mongo->users->findOne(
            ['email' => $this->session->user['email']]
        );
        $key = 'example_key';
        $decoded = JWT::decode($result->token, new Key($key, 'HS256'));
        $GLOBALS['userMail'] = $decoded->email;
        $GLOBALS['userNm'] = $decoded->name;
        if ($this->request->has('addWeb')) {
            $data = $this->request->getPost();
            $this->mongo->webhook->insertOne([
                'name' => $data['WebHook'],
                'url' => $data['url'],
                'key' => $data['key'],
                'event' => $data['action'],
            ]);
        }
    }

    /**
     * Webhook function
     */
    public function errorAction(): void
    {
    }

    /**
     * signOut function
     */
    public function signOutAction(): void
    {
        $this->session->remove('user');
        $this->session->destroy();
        $this->response->redirect('index');
    }
}
