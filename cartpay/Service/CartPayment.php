<?php

namespace Cartpay\Service;

use Illuminate\Support\Facades\Log;

class CartPayment
{

    private $api_payment;


    function __construct()
    {
        $this->api_payment = env('PAYMENT_API', '');
    }


    public function calculate_cart($token)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->api_payment.$token,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        Log::debug($response);

        return $response;
    }
}
