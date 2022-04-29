<?php
namespace Tribal\Services\FAC;

class Modifications {
    
    private $client;
    


    function __construct($client_auth)
    {
        $this->client = $client_auth;
    }

    public function operation($previusDetails, $type){

        switch ($type) {
            case 'value':
                $previusDetails['ModificationType'] = ModificationTypes::Capture;
                break;
            case 'value':
                $previusDetails['ModificationType'] = ModificationTypes::Refund;
                break;
            case 'value':
                $previusDetails['ModificationType'] = ModificationTypes::Reversal;
                break;
            default:
            $previusDetails['ModificationType'] = ModificationTypes::Capture;
                break;
        }

        $TransactionModificationRequest = array(
            'Request' => $previusDetails
            
        );

        $result = $this->client->call('TransactionModification', [$TransactionModificationRequest]);

        return json_decode(json_encode($result), true);
    }
}