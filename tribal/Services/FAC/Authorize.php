<?php
namespace Tribal\Services\FAC;

class Authorize {

    private $client;
    


    function __construct($client_auth)
    {
        $this->client = $client_auth;
    }

    public function payment($cardDetails, $transactionDetails, $billingDetails, $fraudDetails){

        $transactionDetails['TransactionCode'] = '8';

        $authorizeRequest = array(
            'Request' => array(
                'TransactionDetails' => $transactionDetails,
                'CardDetails' => $cardDetails,
                'BillingDetails' => $billingDetails,
                'ShippingDetails' => null,
                'FraudDetails' => $fraudDetails,
                'ThreeDSecureDetails' => null
            )
        );
        
        Log::debug($authorizeRequest);

        $result = $this->client->call('Authorize', [$authorizeRequest]);
        return json_decode(json_encode($result), true);
    }
    
}