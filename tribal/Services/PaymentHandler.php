<?php 
namespace Tribal\Services;

use \GuzzleHttp\Client;
use \Meng\AsyncSoap\Guzzle\Factory;

use Tribal\Services\FAC\Authorize;
use Tribal\Services\FAC\Authorize3DS;
use Tribal\Services\FAC\Modifications;

class PaymentHandler
{

    private $signature;

    private $amount;
    private $orderNumber;
    private $soapOptions;
    private $wsdlurl;

    private $password;
    private $facId;
    private $acquirerId;
    private $currency;

    private $client;

    private $transactionDetails = array();
    private $cardDetails = array(); 
    private $billingDetails = array();

    private $previousTransactionDetails = array();

    private $return_3ds_page;
    

    function __construct() {

        $this->acquirerId = $this->env('FAC_ACQUIRE_ID', '');
        $this->password = $this->env('FAC_PASSWORD', '');
        $this->currency = $this->env('FAC_CURRENCY', '');
        $this->facId = $this->env('FAC_ID', '');
        $this->wsdlurl = $this->env('FAC_WSDL', '');
        $this->return_3ds_page = $this->env('FAC_3DS_RETURN_URL', '');

        $this->soapOptions = array(
            'location' => $this->env('FAC_URL', ''),
            'soap_version' => SOAP_1_1,
            'exceptions' => 0,
            'trace' => 1,
            'cache_wsdl' => WSDL_CACHE_NONE
        );

        $factory = new Factory();

        $this->client = $factory->create(
            new Client(),
            $this->wsdlurl, 
            $this->soapOptions
        );

    }


    public function transaction($transaction_data)
    {
        
        $this->build_transaction_data($transaction_data);
        $data_return = array();

        if($this->isAmex($transaction_data['card_number'])){
            $data_return =  array(
                'is_3ds' => false,
                'data'  => $this->AuthorizeRequest()
            );
            
        }else{
            $data_return =  array(
                'is_3ds' => true,
                'data'  => $this->Authorize3DSRequest(),
                'page_return_form' => array_merge(
                    $this->cardDetails, 
                    $this->transactionDetails,
                    $this->billingDetails,
                    $this->fraudDetails
                )
            );
        }        

        return $data_return;
    }

    public function modification($oparation_data)
    {
        $this->orderNumber = $oparation_data['orderNumber'];
        $this->amount = $this->convertAmount($oparation_data['amount']);
        $this->build_modification_data();

        $modification = new Modifications($this->client);

        return $modification->operation(
            $this->previousTransactionDetails, 
            $oparation_data['operation']
        );

    }

    private function build_modification_data(){
        $this->previousTransactionDetails = array(
            'AcquirerId' => $this->acquirerId,
            'Amount' => $this->amount,
            'CurrencyExponent' => 2,
            'MerchantId' => $this->facId,
            'ModificationType' => null,
            'OrderNumber' => $this->orderNumber,
            'Password' => $this->password
        );
    }


    private function build_transaction_data($transaction_data)
    {
        $this->orderNumber = $transaction_data['orderNumber'];
        $this->amount = $this->convertAmount($transaction_data['amount']);
        $this->sessionId= $transaction_data['session_id'];
        $this->signature = $this->sign(
            $this->password, 
            $this->facId, 
            $this->acquirerId, 
            $this->orderNumber, 
            $this->amount,
            $this->currency
        );

        $this->cardDetails = array(
            'CardCVV2' => $transaction_data['card_cvv2'],
            'CardExpiryDate' => $transaction_data['exp_date'],
            'CardNumber' => $transaction_data['card_number']
        );

        $this->transactionDetails = array(
            'AcquirerId' => $this->acquirerId,
            'Amount' => $this->amount,
            'Currency' => $this->currency,
            'CurrencyExponent' => 2,
            'IPAddress' => '',
            'MerchantId' => $this->facId,
            'OrderNumber' => $this->orderNumber,
            'Signature' => $this->signature,
            'SignatureMethod' => 'SHA1',
            'TransactionCode' => '0'
        );

        $this->billingDetails = array(
            'BillToAddress' => '1233 Whiss Vlsd.',
            'BillToAddress2' => 'Unt 13',
            'BillToZipPostCode' => '23221',
            'BillToFirstName' => 'John',
            'BillToLastName' => 'Some',
            'BillToCity' => 'Boston',
            'BillToState' => 'NY',
            'BillToCountry' => '543',
            'BillToCounty' => '234433444',
            'BillToEmail' => 'stoj@tribalworldwide.gt'
        );

        $this->fraudDetails = array(
            'SessionId' => $this->sessionId
        );

        
    }

    private function AuthorizeRequest(){
        
        $authorize = new Authorize($this->client);

        return $authorize->payment(
            $this->cardDetails, 
            $this->transactionDetails,
            $this->billingDetails,
            $this->fraudDetails
        );
        
    }

    private function Authorize3DSRequest(){

        $authorize = new Authorize3DS($this->client);

        return $authorize->payment(
            $this->cardDetails, 
            $this->transactionDetails,
            $this->billingDetails,
            $this->fraudDetails,
            $this->return_3ds_page
        );

    }

    // Sign a FAC Authorize message
    public function sign($passwd, $facId, $acquirerId, $orderNumber, $amount, $currency)
    {
        $stringtohash =
        $passwd.$facId.$acquirerId.$orderNumber.$amount.$currency;
        $hash = sha1($stringtohash, true);
        $signature = base64_encode($hash);

        return $signature;
    }

    public function convertAmount($amount){
        $amount = number_format( $amount, 0 );
        $amount = strval($amount);
        $amount = str_replace('.', '', $amount);
        $amount = str_replace(',', '', $amount);
        $mask = substr_replace('000000000000', '', 12 - strlen( $amount) , strlen( $amount) );
        $amount = substr_replace($mask, $amount, 12 - strlen( $amount) , 1 );
        return $amount;
    }

    /*
        Check if the cardnumber is American Express
    */

    private function isAmex($pan)
    {

        // 
        $amex_regex = "/^3[47][0-9]{0,}$/";

        if (preg_match($amex_regex, $pan)) {
            return true;
        }
        return false;
    }

    private function env($key){
        return $_ENV[$key];
    }

    

}