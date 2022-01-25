<?php

namespace Cartpay\Service;

class CartPayment
{

    private $api_payment;


    function __construct()
    {
        $this->api_payment = $this->env('PAYMENT_API', '');
    }


    public function calculate_cart($data)
    {



        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->api_payment,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode(array('id' => $data)),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);


        return $response;
    }

    private function env($key){
        return $_ENV[$key];
    }
}
