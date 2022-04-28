<?php

namespace App\Components;

use Phalcon\Di\Injectable;
use Firebase\JWT\JWT;

/**
 * Loader Class
 */
final class Helper extends Injectable
{
    /**
     * process function
     */
    public function validate(): void
    {
        if (! isset($this->session->user)) {
            $this->response->redirect(BASE_URI . "login");
        }
    }

    public function validateAdmin(): void
    {
        if ($this->session->user["role"] !== "admin") {
            $this->response->redirect(BASE_URI . "user/error");
        }
    }

    /**
     * tokenValidate function
     * to get the token based on name and email
     */
    public function tokenValidate($name, $email)
    {
        $key = "example_key";
        $payload = [
            "iss" => "http://example.org",
            "aud" => "https://target.phalcon.io",
            "iat" => 1356999524,
            "nbf" => 1357000000,
            "role" => 'user',
            "name" => $name,
            "email" => $email,
            "fsf" => "https://phalcon.io"
        ];
        return JWT::encode($payload, $key, 'HS256');
    }


    public function getdata($data)
    {
        if ($data === "delete") {
            $val = $this->request->get('id');
        } elseif ($data === "update") {
            $products = $this->mongo->product->find()->toArray();
            $val = json_encode($products);
        } else {
            $options = [
                "limit" => 1,
                "sort" => ["_id" => -1]
            ];
            $data = $this->mongo->product->findOne([], $options);
            $val = json_encode($data);
        }
        return $val;
    }
}
