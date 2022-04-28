<?php

namespace Api\Components;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Phalcon\Mvc\Micro\MiddlewareInterface;
use Phalcon\Mvc\Micro;
use Phalcon\Http\Response;
use Phalcon\Http\Request;
use MongoDB\BSON\ObjectID;

$userMail = '';         //Global Variable to declare the current user email
$UserNm = '';           //Global Variable to declare the current user name


/**
 * MiddleWare class
 * In order to authorize and validate user based on the token
 */
class MiddleWare implements MiddlewareInterface
{
    /**
     * authorize function
     * In order to authorize the registering  user
     * @param [type] $app
     * @return void
     */
    public function authorize()
    {
        $key = "example_key";
        $payload = array(
            "iss" => "http://example.org",
            "aud" => "https://target.phalcon.io",
            "iat" => 1356999524,
            "nbf" => 1357000000,
            "role" => 'user',
            "email" => "abc@xyz",
            "name" => "ashu",
            "fsf" => "https://phalcon.io"
        );
        $jwt = JWT::encode($payload, $key, 'HS256');
        return $jwt;
    }

    /**
     * Validate function
     * To validate the user based on the token
     * @param [type] $token
     * @param [type] $app
     * @return void
     */
    public function validate($token, $app)
    {
        $key = "example_key";
        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            $GLOBALS["userMail"] = $decoded->email;
            $GLOBALS["userNm"] = $decoded->name;
        } catch (\Exception $e) {
            $app->response->setStatusCode(400)
                ->setJsonContent($e->getMessage())
                ->send();
            die;
        }
    }

    /**
     * To handle request response and show the errors if token is invalid
     * @param Micro $app
     * @return void
     */
    public function call(Micro $app)
    {
        $check = explode('/', $app->request->get()['_url'])[2];
        if ($check == "create") {
            $response = new Response();
            $request = new Request();
            $getproduct = json_decode(json_encode($request->getJsonRawBody()), true);
            $checkproduct_id = ($getproduct['product_id']);
            try {
                $result = $app->mongo->product->findOne([
                    "_id" => new ObjectID($checkproduct_id)
                ]);
                if (!empty($result)) {

                    if ($getproduct['quantity'] < 0 || $getproduct['quantity'] > $result->stock) {
                        if ($getproduct['quantity'] < 0) {
                            $response->setStatusCode(404, 'Not Available');
                            $response->setJsonContent(" Quantity Can't be Negative'");
                        } else {
                            $response->setStatusCode(404, 'Not Available');
                            $response->setJsonContent("This much Quantity not available for this Product!!'");
                        }
                        $response->send();
                        die;
                    }
                    $token =  $app->request->get("token");
                    $this->validate($token, $app);
                } else {
                    $response->setStatusCode(404, 'No Match Found ');
                    $response->setJsonContent("Invalid Product ID");
                    $response->send();
                    die;
                }
            } catch (\Exception $e) {
                $response->setStatusCode(404, 'Please Enter Valid Product ID');
                $response->setJsonContent("Please Enter Valid Product ID!!");
                $response->send();
                die;
            }
        } elseif ($check == "createToken") 
        {  
            
            $token = $this->authorize();
            die($token);
        }
        else {   
            $token =  $app->request->get("token");
            $this->validate($token, $app);
        }
    }
}
