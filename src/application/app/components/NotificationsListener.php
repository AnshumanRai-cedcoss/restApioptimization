<?php

namespace App\Components;

use Phalcon\Di\Injectable;
use GuzzleHttp\Client;

final class NotificationsListener extends Injectable
{
    /**
     * Check function
     * to update an existing product
     */
    public function check($data): void
    {
        $value = $data->getdata();
        $webhooks = $this->mongo->webhook->find()->toArray();
        $obj = new Helper();
        foreach ($webhooks as $key => $val) { 
            if (in_array($value, (array) $val["event"])) {
                $result = $obj->getData($value);
                $client = new Client();
                $client->request(
                    'POST',
                    $val['url'] . $value,
                    ["form_params" => ["data" => $result]]
                );
            }
        }
    }
}
