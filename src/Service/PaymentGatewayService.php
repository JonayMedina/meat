<?php

namespace App\Service;

use App\Entity\AboutStore;
use App\Repository\AboutStoreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PaymentGatewayService
{
    const ENDPOINT = "https://epaytestvisanet.com.gt/paymentcommerce.asmx?WSDL";

    const MESSAGE_TYPE_SELL = "0200";

    const MESSAGE_TYPE_ANNULMENT = "0202";

    const MESSAGE_TYPE_REVERSE = "0400";

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
     * @var bool
     */
    private $isTestEnvironment = false;

    /**
     * Response code collection.
     * @var string[]
     */
    private $responseCodes = [
        "00" => 'Aprobada',
        "01" => 'Refiérase al Emisor',
        "02" => 'Refiérase al Emisor',
        "05" => 'Transacción No Aceptada',
        "13" => 'Monto Inválido',
        "19" => 'Transacción no realizada, intente de nuevo',
        "31" => 'Tarjeta no soportada por switch',
        "35" => 'Transacción ya ha sido ANULADA',
        "36" => 'Transacción a ANULAR no EXISTE',
        "37" => 'Transacción de ANULACION REVERSADA',
        "38" => 'Transacción a ANULAR con Error',
        "41" => 'Tarjeta Extraviada',
        "43" => 'Tarjeta Robada',
        "51" => 'No tiene fondos disponibles',
        "58" => 'Transacción no permitida en la terminal',
        "89" => 'Terminal inválida',
        "91" => 'Emisor no disponible',
        "94" => 'Transacción duplicada',
        "96" => 'Error del sistema, intente más tarde',
    ];

    /**
     * @var AboutStoreRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * PaymentGatewayService constructor.
     * @param AboutStoreRepository $repository
     * @param EntityManagerInterface $entityManager
     * @param $epayGateWayIP
     * @param $epayTerminalID
     * @param $epayMerchant
     * @param $epayMerchantUser
     * @param $epayMerchantPassword
     */
    public function __construct(AboutStoreRepository $repository, EntityManagerInterface $entityManager, $epayGateWayIP, $epayTerminalID, $epayMerchant, $epayMerchantUser, $epayMerchantPassword)
    {
        $this->repository = $repository;
        $this->entityManager = $entityManager;

        $this->shopperIP = $this->findShopperIP();
        $this->merchantServerIP = $this->findMerchantServerIP();
        $this->posEntryMode = self::POS_MODE_NORMAL;

        /** We are using Dependency injection here... */
        $this->paymentgwIP = $epayGateWayIP;
        $this->terminalId = $epayTerminalID;
        $this->merchant = $epayMerchant;
        $this->merchantUser = $epayMerchantUser;
        $this->merchantPasswd = $epayMerchantPassword;
    }

    public function pay($amount, $cardHolder, $cardNumber, $expDate, $cvv): array
    {
        $response = $this
            ->configureSell()
            ->configureAuditNumber()
            ->setPan($cardNumber)
            ->setExpDate("$expDate")
            ->setAmount($amount)
            ->setCvv2($cvv)
            ->request();

        $response['response']['responseMessage'] = $this->getResponseMessage($response['response']['responseCode']);
        $response['response']['cardHolder'] = $cardHolder;
        $response['response']['cardNumber'] = $this->maskCreditCard($cardNumber);
        $response['response']['dateTime'] = date('c');

        return $response['response'];
    }

    public function reverse($amount, $cardNumber, $expDate, $cvv)
    {
        // TODO: Implement a reverse method configuration here.
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
                    'merchantUser' => $this->merchantUser,
                    'merchantPasswd' => $this->merchantPasswd,
                    'terminalId' => $this->terminalId,
                    'merchant' => $this->merchant,
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
     * @return PaymentGatewayService
     */
    public function useTestEnvironment(): PaymentGatewayService
    {
        $this->isTestEnvironment = true;

        /** Test variables */
        $this->paymentgwIP = "190.149.69.135";
        $this->terminalId = "77788881";
        $this->merchant = "00575123";
        $this->merchantUser = "76B925EF7BEC821780B4B21479CE6482EA415896CF43006050B1DAD101669921";
        $this->merchantPasswd = "DD1791DB5B28DDE6FBC2B9951DFED4D97B82EFD622B411F1FC16B88B052232C7";

        return $this;
    }

    /**
     * Generate audit number.
     * @return string
     */
    private function generateAuditNumber()
    {
        $aboutStore = $this->repository->findLatest();

        if (!$aboutStore instanceof AboutStore) {
            throw new BadRequestHttpException('Invalid audit number. (Try to create a new settings entity.)');
        }

        if (!$this->isTestEnvironment) {
            $lastAuditNumber = $aboutStore->getLastAuditNumber();
            $newAuditNumber = str_pad((int)$lastAuditNumber+1, 6, '0', STR_PAD_LEFT);

            if ((int)$newAuditNumber > 999999) {
                $newAuditNumber = str_pad(1, 6, '0', STR_PAD_LEFT);
            }

            $aboutStore->setLastAuditNumber($newAuditNumber);
            $this->entityManager->flush();

            return $newAuditNumber;
        }

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

    /**
     * Return message as string.
     * @param $code
     * @return string|null
     */
    private function getResponseMessage($code): ?string
    {
        return $this->responseCodes[$code] ?? null;
    }

    /**
     * Mask credit card number.
     * @param $number
     * @param string $maskingCharacter
     * @return string
     */
    private function maskCreditCard($number, $maskingCharacter = 'X') {
        return chunk_split(str_repeat($maskingCharacter, strlen($number) - 4) . substr($number, -4), 4, ' ');
    }

    /**
     * Configure audit number.
     */
    private function configureAuditNumber(): self
    {
        $this->auditNumber = $this->generateAuditNumber();

        return $this;
    }
}
