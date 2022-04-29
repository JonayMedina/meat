<?php
namespace Tribal\Services\FAC;

class Authorize3DS {

    private $client;
    
    function __construct($client_auth)
    {
        $this->client = $client_auth;
    }

    public function payment($cardDetails, $transactionDetails, $billingDetails, $fraudDetails, $return_3ds_page){

        $authorizeRequest = array(
            'Request' => array(
                'MerchantResponseURL' => $return_3ds_page,
                'TransactionDetails' => $transactionDetails,
                'CardDetails' => $cardDetails,
                'BillingDetails' => $billingDetails,
                'ShippingDetails' => null,
                'ThreeDSecureDetails' => null
            )
        );

        $result = $this->client->call('Authorize3DS', [$authorizeRequest]);
        return json_decode(json_encode($result), true);
    }

}