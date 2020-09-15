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

        $response = $this->paymentService
            ->useTestEnvironment()
            ->pay("1500", "MeatHouse User", "4000000000000416", "1912", "123");

        $table = new Table($output);
        $table
            ->setHeaders(['Card Holder', 'Card Number', 'Audit Number', 'Reference Number', 'Authorization Number', 'Response Code', 'Response Message', 'message Type'])
            ->setRows([
                [$response['cardHolder'], $response['cardNumber'], $response['auditNumber'], $response['referenceNumber'], $response['authorizationNumber'], $response['responseCode'], $response['responseMessage'], $response['messageType']],
            ]);

        $table->render();

        $io->comment('Praga Web Studio Rocks!');

        return 0;
    }
}
