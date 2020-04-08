<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PaymentGatewayService
{
    const ENDPOINT = "https://epaytestvisanet.com.gt/paymentcommerce.asmx?WSDL";

    const MESSAGE_TYPE_SELL = "0200";

    const MESSAGE_TYPE_ANNULMENT = "0202";

    const MESSAGE_TYPE_REVERSE = "0400";

    const PAYMENT_GATEWAY_IP = "190.149.69.135";

    const POS_MODE_NORMAL = "012";

    const POST_MODE_BAND_READER = "022";

    const TIMEOUT = 10;

    /**
     * @var string
     */
    private $posEntryMode;

    /**
     * @var string
     */
    private $pan;

    /**
     * @var string
     */
    private $expdate;

    /**
     * @var string
     */
    private $amount;

    /**
     * @var string
     */
    private $track2Data = "";

    /**
     * @var string
     */
    private $cvv2;

    /**
     * @var string
     */
    private $paymentgwIP;

    /**
     * @var string
     */
    private $shopperIP;

    /**
     * @var string
     */
    private $merchantServerIP;

    /**
     * @var string
     */
    private $merchantUser;

    /**
     * @var string
     */
    private $merchantPasswd;

    /**
     * @var string
     */
    private $terminalId;

    /**
     * @var string
     */
    private $merchant;

    /**
     * @var string
     */
    private $messageType;

    /**
     * @var string
     */
    private $auditNumber;

    /**
     * @var string
     */
    private $additionalData = "";

    /**
     * PaymentGatewayService constructor.
     */
    public function __construct()
    {
        $this->auditNumber = $this->generateAuditNumber();
        $this->shopperIP = $this->findShopperIP();
        $this->merchantServerIP = $this->findMerchantServerIP();
        $this->posEntryMode = self::POS_MODE_NORMAL;

        $this->paymentgwIP = self::PAYMENT_GATEWAY_IP;
        $this->terminalId = "77788881";
        $this->merchant = "00575123";
    }

    /**
     * Execute request.
     */
    public function request()
    {
        try {
            // Config
            $client = new \nusoap_client(self::ENDPOINT, 'wsdl',  false, false, false, false, self::TIMEOUT);
            $client->soap_defencoding = 'UTF-8';
            $client->decode_utf8 = FALSE;

            // Calls
            return $client->call('AuthorizationRequest', $this->getParameters());
        } catch (\Exception $exception) {
            // Config
            $client = new \nusoap_client(self::ENDPOINT, 'wsdl',  false, false, false, false, self::TIMEOUT);
            $client->soap_defencoding = 'UTF-8';
            $client->decode_utf8 = FALSE;
            // Configure reverse
            $this->configureReverse();

            // Calls
            return $client->call('AuthorizationRequest', $this->getParameters());
        }
    }

    /**
     * Return request params.
     * @return array
     */
    public function getParameters()
    {
        return [
            [
                'AuthorizationRequest' => [
                    'posEntryMode' => $this->getPosEntryMode(),
                    'pan' => $this->getPan(),
                    'expdate' => $this->getExpDate(),
                    'amount' => $this->getAmount(),
                    'track2Data' => $this->getTrack2Data(),
                    'cvv2' => $this->getCvv2(),
                    'paymentgwIP' => $this->getPaymentgwIP(),
                    'shopperIP' => $this->getShopperIP(),
                    'merchantServerIP' => $this->getMerchantServerIP(),
                    'merchantUser' => $this->getMerchantUser(),
                    'merchantPasswd' => $this->getMerchantPasswd(),
                    'terminalId' => $this->getTerminalId(),
                    'merchant' => $this->getMerchant(),
                    'messageType' => $this->getMessageType(),
                    'auditNumber' => $this->getAuditNumber(),
                    'additionalData' => $this->getAdditionalData(),
                ]
            ]
        ];
    }

    /**
     * Set band reader mode ON.
     * @return PaymentGatewayService
     */
    public function bandReaderModeOn()
    {
        $this->posEntryMode = self::POST_MODE_BAND_READER;

        return $this;
    }

    /**
     * Configure request as sell.
     * @return PaymentGatewayService
     */
    public function configureSell()
    {
        $this->setMessageType(self::MESSAGE_TYPE_SELL);

        return $this;
    }

    /**
     * Configure request as annulment.
     * @return PaymentGatewayService
     */
    public function configureAnnulment()
    {
        $this->setMessageType(self::MESSAGE_TYPE_ANNULMENT);

        return $this;
    }

    /**
     * Configure request as reverse.
     * @return PaymentGatewayService
     */
    public function configureReverse()
    {
        $this->setMessageType(self::MESSAGE_TYPE_REVERSE);

        return $this;
    }

    /**
     * @return string
     */
    public function getPosEntryMode(): ?string
    {
        return $this->posEntryMode;
    }

    /**
     * @param string $posEntryMode
     * @return PaymentGatewayService
     */
    public function setPosEntryMode(?string $posEntryMode): PaymentGatewayService
    {
        $this->posEntryMode = $posEntryMode;
        return $this;
    }

    /**
     * @return string
     */
    public function getPan(): ?string
    {
        return $this->pan;
    }

    /**
     * @param string $pan
     * @return PaymentGatewayService
     */
    public function setPan(?string $pan): PaymentGatewayService
    {
        $this->pan = $pan;
        return $this;
    }

    /**
     * @return string
     */
    public function getExpDate(): ?string
    {
        return $this->expdate;
    }

    /**
     * @param string $expdate
     * @return PaymentGatewayService
     */
    public function setExpDate(?string $expdate): PaymentGatewayService
    {
        $this->expdate = $expdate;
        return $this;
    }

    /**
     * @return string
     */
    public function getAmount(): ?string
    {
        return $this->amount;
    }

    /**
     * @param string $amount
     * @return PaymentGatewayService
     */
    public function setAmount(?string $amount): PaymentGatewayService
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getTrack2Data(): ?string
    {
        return $this->track2Data;
    }

    /**
     * @param string $track2Data
     * @return PaymentGatewayService
     */
    public function setTrack2Data(?string $track2Data): PaymentGatewayService
    {
        $this->track2Data = $track2Data;
        return $this;
    }

    /**
     * @return string
     */
    public function getCvv2(): ?string
    {
        return $this->cvv2;
    }

    /**
     * @param string $cvv2
     * @return PaymentGatewayService
     */
    public function setCvv2(?string $cvv2): PaymentGatewayService
    {
        $this->cvv2 = $cvv2;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentgwIP(): ?string
    {
        return $this->paymentgwIP;
    }

    /**
     * @return string
     */
    public function getShopperIP(): ?string
    {
        return $this->shopperIP;
    }

    /**
     * @return string
     */
    public function getMerchantServerIP(): ?string
    {
        return $this->merchantServerIP;
    }

    /**
     * @return string
     */
    public function getMerchantUser(): ?string
    {
        return $this->merchantUser;
    }

    /**
     * @param string $merchantUser
     * @return PaymentGatewayService
     */
    public function setMerchantUser(?string $merchantUser): PaymentGatewayService
    {
        $this->merchantUser = $merchantUser;
        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantPasswd(): ?string
    {
        return $this->merchantPasswd;
    }

    /**
     * @param string $merchantPasswd
     * @return PaymentGatewayService
     */
    public function setMerchantPasswd(?string $merchantPasswd): PaymentGatewayService
    {
        $this->merchantPasswd = $merchantPasswd;
        return $this;
    }

    /**
     * @return string
     */
    public function getTerminalId(): ?string
    {
        return $this->terminalId;
    }

    /**
     * @return string
     */
    public function getMerchant(): ?string
    {
        return $this->merchant;
    }

    /**
     * @return string
     */
    public function getMessageType(): ?string
    {
        return $this->messageType;
    }

    /**
     * @param string $messageType
     * @return PaymentGatewayService
     */
    public function setMessageType(?string $messageType): PaymentGatewayService
    {
        if (!in_array($messageType, [
            self::MESSAGE_TYPE_SELL,
            self::MESSAGE_TYPE_ANNULMENT,
            self::MESSAGE_TYPE_REVERSE
        ])) {
            throw new BadRequestHttpException('Invalid type message type.');
        }

        $this->messageType = $messageType;
        return $this;
    }

    /**
     * @return string
     */
    public function getAuditNumber(): ?string
    {
        return $this->auditNumber;
    }

    /**
     * @return string
     */
    public function getAdditionalData(): ?string
    {
        return $this->additionalData;
    }

    /**
     * @param string $additionalData
     * @return PaymentGatewayService
     */
    public function setAdditionalData(?string $additionalData): PaymentGatewayService
    {
        $this->additionalData = $additionalData;
        return $this;
    }

    /**
     * Generate audit number.
     * @return string
     */
    private function generateAuditNumber()
    {
        // TODO: Implement a real audit number generator.
        return "990628";
    }

    /**
     * Find end user IP.
     */
    private function findShopperIP()
    {
        $request = Request::createFromGlobals();

        return $request->getClientIp();
    }

    /**
     * Find Server IP.
     */
    private function findMerchantServerIP()
    {
        return gethostbyname(gethostname());
    }
}
