<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Doctrine\ORM\QueryBuilder;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Shipping\Model\ShipmentInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DashboardService
 * @package App\Service
 */
class DashboardService
{
    /**
     * Date modifier, for dashboard start date.
     */
    const START_DATE_MODIFIER = '-6 months';

    /**
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var string $startDate
     */
    private $startDate;

    /**
     * @var string $endDate
     */
    private $endDate;

    /**
     * @var int $customerTotal
     */
    private $customerTotal = null;

    /**
     * @var int $orderTotal
     */
    private $orderTotal = null;

    /**
     * @var array $genderChartData
     */
    private $genderChartData = [];

    /**
     * @var array $userAgeChartData
     */
    private $userAgeChartData = [];

    /**
     * @var array $purchasesByUserData
     */
    private $purchasesByUserData = [];

    /**
     * @var array $numberOfOrdersChartData
     */
    private $numberOfOrdersChartData = [];

    /**
     * @var float $averageRating
     */
    private $averageRating;

    /**
     * @var array $purchasesInDateRangeChartData
     */
    private $purchasesInDateRangeChartData = [];

    /**
     * DashboardService constructor.
     * @param ContainerInterface $container
     * @param LoggerInterface $logger
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger = $logger;

        $this->customerCount();
        $this->orderCount();

        $this
            ->retrieveGenderChartData()
            ->retrieveUserAgeChartData()
            ->retrievePurchasesByUserChartData()
            ->retrieveNumberOfOrdersChartData()
            ->calculateAverageRating();
    }

    /**
     * Return number of customers.
     * @param bool $returnQueryBuilder
     * @return QueryBuilder|null
     */
    private function customerCount($returnQueryBuilder = false): ?QueryBuilder
    {
        $queryBuilder = $this->container->get('sylius.repository.customer')
            ->createQueryBuilder('customer')
            ->select('COUNT(customer)');

        try {
            if ($returnQueryBuilder) {
                return $queryBuilder;
            }

            $this->customerTotal = $queryBuilder
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        return null;
    }

    /**
     * Count total of orders.
     * @param $returnQueryBuilder
     * @return QueryBuilder|null
     */
    private function orderCount($returnQueryBuilder = false): ?QueryBuilder
    {
        $queryBuilder = $this->container->get('sylius.repository.order')
            ->createQueryBuilder('o')
            ->select('COUNT(o)');

        try {
            if ($returnQueryBuilder) {
                return $queryBuilder;
            }

            $this->orderTotal = $queryBuilder
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        return null;
    }

    /**
     * Retrieve Gender Chart Data.
     */
    private function retrieveGenderChartData(): self
    {
        $data = [];
        $genders = [
            CustomerInterface::FEMALE_GENDER,
            CustomerInterface::MALE_GENDER,
            CustomerInterface::UNKNOWN_GENDER
        ];

        foreach ($genders as $gender) {
            $counter = null;

            try {
                $counter = $this->customerCount(true)
                    ->andWhere('customer.gender = :gender')
                    ->setParameter('gender', $gender)
                    ->getQuery()
                    ->getSingleScalarResult();
            } catch (\Exception $exception) {
                $counter = null;
                $this->logger->error($exception->getMessage());
            }

            $data[$gender] = $counter;
        }

        $this->genderChartData = $data;

        return $this;
    }

    /**
     * Retrieve User Age Chart Data.
     */
    private function retrieveUserAgeChartData(): self
    {
        $data = [];
        $sections = [
            ['min' => 1, 'max' => 17],
            ['min' => 18, 'max' => 24],
            ['min' => 25, 'max' => 34],
            ['min' => 35, 'max' => 44],
            ['min' => 45, 'max' => 1000],
            ['min' => null, 'max' => null],
        ];

        $sql = "SELECT COUNT(customer.id) FROM sylius_customer customer WHERE (YEAR(CURDATE()) - YEAR(customer.birthday)) BETWEEN :min AND :max;";
        $connection = $this->container->get('doctrine')->getManager()->getConnection();

        foreach ($sections as $section) {
            $totalOfUsers = null;
            $min = $section['min'];
            $max = $section['max'];

            $key = $min.$max;

            if ($min == null && $max == null) {
                $key = 'na';
                $sql = "SELECT COUNT(customer.id) FROM sylius_customer customer WHERE (YEAR(CURDATE()) - YEAR(customer.birthday)) IS NULL;";
            }

            try {
                $stmt = $connection->prepare($sql);

                if ($min != null && $max != null) {
                    $stmt->bindValue('min', $min);
                    $stmt->bindValue('max', $max);
                }

                $stmt->execute();
                $totalOfUsers = $stmt->fetchColumn();
            } catch (\Exception $exception) {
                $totalOfUsers = null;
                $this->logger->error($exception->getMessage());
            }

            $data[$key] = $totalOfUsers;
        }

        $this->userAgeChartData = $data;

        return $this;
    }

    /**
     * Retrieve purchases by user chart data.
     */
    private function retrievePurchasesByUserChartData(): self
    {
        // with no purchases
        $sql = "SELECT COUNT(DISTINCT c.id) FROM sylius_customer c LEFT JOIN sylius_order o ON c.id = o.customer_id WHERE o.id IS NULL OR o.state != '". OrderInterface::STATE_FULFILLED ."' OR o.payment_state = '". PaymentInterface::STATE_REFUNDED ."' OR o.payment_state = '" . PaymentInterface::STATE_FAILED . "' OR o.payment_state = '". PaymentInterface::STATE_CANCELLED ."'";
        $connection = $this->container->get('doctrine')->getManager()->getConnection();
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $noPurchases = $stmt->fetchColumn();

        // with purchases
        $sql = "SELECT COUNT(DISTINCT c.id) FROM sylius_customer c LEFT JOIN sylius_order o ON c.id = o.customer_id WHERE o.state = '". OrderInterface::STATE_FULFILLED ."' AND o.payment_state != '". PaymentInterface::STATE_REFUNDED ."' AND o.shipping_state = '". ShipmentInterface::STATE_SHIPPED ."'";
        $connection = $this->container->get('doctrine')->getManager()->getConnection();
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $withPurchases = $stmt->fetchColumn();

        $data = [
            'purchases' => $withPurchases,
            'no_purchases' => $noPurchases
        ];

        $this->purchasesByUserData = $data;

        return $this;
    }

    /**
     * Retrieve number of orders chart data.
     */
    private function retrieveNumberOfOrdersChartData(): self
    {
        $data = [];
        $statuses = [
            OrderInterface::STATE_FULFILLED,
            OrderInterface::STATE_CANCELLED,
            OrderPaymentStates::STATE_AWAITING_PAYMENT,
        ];

        foreach ($statuses as $status) {
            $number = null;

            try {
                if ($status === OrderPaymentStates::STATE_AWAITING_PAYMENT) {
                    // Pending...
                    $number = $this->container->get('sylius.repository.order')
                        ->createQueryBuilder('o')
                        ->select('COUNT(o)')
                        ->andWhere('o.paymentState = :status')
                        ->setParameter('status', $status)
                        ->getQuery()
                        ->getSingleScalarResult();

                } else {
                    $number = $this->container->get('sylius.repository.order')
                        ->createQueryBuilder('o')
                        ->select('COUNT(o)')
                        ->andWhere('o.state = :status')
                        ->setParameter('status', $status)
                        ->getQuery()
                        ->getSingleScalarResult();
                }

            } catch (\Exception $exception) {
                $number = null;
                $this->logger->error($exception->getMessage());
            }

            $data[$status] = $number;
        }

        $this->numberOfOrdersChartData = $data;

        return $this;
    }

    /**
     * Calculate order average rating.
     */
    private function calculateAverageRating(): self
    {
        try {
            $averageRating = $this->container->get('sylius.repository.order')
                ->createQueryBuilder('o')
                ->select('AVG(o.rating)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $exception) {
            $averageRating = null;
            $this->logger->error($exception->getMessage());
        }

        $this->averageRating = $averageRating;

        return $this;
    }

    /**
     * Retrieve purchases in date range.
     */
    public function retrievePurchasesInDateRangeChartData(): self
    {
        $dates = [];
        $locale = $this->container->getParameter('locale');
        $localeParts = explode( '_', $locale);

        if ($localeParts[0] == 'es') {
            $locale = 'es_ES';
        }

        try {
            $start    = (new \DateTime($this->getStartDate()))->modify('first day of this month');
            $end      = (new \DateTime($this->getEndDate()))->modify('first day of next month');
            $interval = \DateInterval::createFromDateString('1 month');
            $period   = new \DatePeriod($start, $interval, $end);

            foreach ($period as $dt) {
                $dates[] = [
                    'start' => $dt->format("Y-m-01"),
                    'end' => $dt->format("Y-m-t"),
                ];
            }

            $dates[0]['start'] = $this->getStartDate();
            $dates[count($dates)-1]['end'] = $this->getEndDate();

            // get purchases data
            foreach ($dates as $index => $date) {
                $counter = $this->orderCount(true)
                    ->andWhere('o.createdAt BETWEEN :start AND :end')
                    ->setParameter('start', $date['start'])
                    ->setParameter('end', $date['end'])
                    ->getQuery()
                    ->getSingleScalarResult();

                $dates[$index]['purchases'] = $counter;

                setlocale(LC_ALL, $locale);
                $dateObject = new \DateTime($dates[$index]['start']);

                $dates[$index]['label'] = ucfirst(strftime("%B", $dateObject->getTimestamp()));
            }

        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        $this->purchasesInDateRangeChartData = $dates;

        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerTotal(): ?int
    {
        return $this->customerTotal;
    }

    /**
     * Return gender chart data.
     * @return array
     */
    public function getGenderChartData(): array
    {
        return $this->genderChartData;
    }

    /**
     * Return users age chart data.
     * @return array
     */
    public function getUserAgeChartData(): array
    {
        return $this->userAgeChartData;
    }

    /**
     * Return purchases by users data.
     * @return array
     */
    public function getPurchasesByUserData(): array
    {
        return $this->purchasesByUserData;
    }

    /**
     * @return array
     */
    public function getNumberOfOrdersChartData(): array
    {
        return $this->numberOfOrdersChartData;
    }

    /**
     * Return order counter.
     * @return int
     */
    public function getOrderTotal(): int
    {
        return $this->orderTotal;
    }

    /**
     * Return calculated average rating.
     * @return float
     */
    public function getAverageRating(): ?float
    {
        return $this->averageRating;
    }

    /**
     * @return string
     */
    public function getStartDate(): ?string
    {
        $date = str_replace('/', '-', $this->startDate );

        return date("Y-m-d", strtotime($date));
    }

    /**
     * @param string $startDate
     * @return DashboardService
     */
    public function setStartDate(?string $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getEndDate(): ?string
    {
        $date = str_replace('/', '-', $this->endDate);

        return date("Y-m-d", strtotime($date));
    }

    /**
     * @param string $endDate
     * @return DashboardService
     */
    public function setEndDate(?string $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Purchases in date range.
     * @return array
     */
    public function getPurchasesInDateRangeChartData(): array
    {
        return $this->purchasesInDateRangeChartData;
    }
}
