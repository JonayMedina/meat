<?php

namespace App\Command;

use App\Service\PaymentGatewayService;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VisaTestCommand extends Command
{
    protected static $defaultName = 'app:visa:test';

    /**
     * @var PaymentGatewayService
     */
    private $paymentService;

    /**
     * VisaTestCommand constructor.
     * @param PaymentGatewayService $paymentService
     */
    public function __construct(PaymentGatewayService $paymentService)
    {
        $this->paymentService = $paymentService;

        parent::__construct();
    }

    /**
     * Configure this command.
     */
    protected function configure()
    {
        $this->setDescription('Test Visa Payment Gateway');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $result = $this->paymentService
            ->configureSell()
            ->setPan("4000000000000416")
            ->setExpDate("1912")
            ->setAmount("1500")
            ->setCvv2("123")
            ->setMerchantUser("76B925EF7BEC821780B4B21479CE6482EA415896CF43006050B1DAD101669921")
            ->setMerchantPasswd("DD1791DB5B28DDE6FBC2B9951DFED4D97B82EFD622B411F1FC16B88B052232C7")
            ->request();

        $response = $result['response'];

        $table = new Table($output);
        $table
            ->setHeaders(['Audit Number', 'Reference Number', 'Authorization Number', 'Response Code', 'message Type'])
            ->setRows([
                [$response['auditNumber'], $response['referenceNumber'], $response['authorizationNumber'], $response['responseCode'], $response['messageType']],
            ]);

        $table->render();

        $io->comment('Praga Web Studio Rocks!');

        return 0;
    }
}
