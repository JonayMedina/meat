<?php

namespace App\Controller\Admin;

use App\Entity\Order\Order;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Intl\Currencies;
use Symfony\Component\Routing\Annotation\Route;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Shipping\Model\ShipmentInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Sylius\Component\Currency\Context\CurrencyContextInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// Include PhpSpreadsheet required namespaces
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\Writer\Pdf;

/**
 * Class OrderController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class OrderController extends AbstractController
{
    const PAGINATOR_LIMIT = 20;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var PaginatorInterface $paginator
     */
    private $paginator;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * CouponController constructor.
     * @param LoggerInterface $logger
     * @param PaginatorInterface $paginator
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(LoggerInterface $logger, PaginatorInterface $paginator, TranslatorInterface $translator, EntityManagerInterface $entityManager)
    {
        $this->logger = $logger;
        $this->paginator = $paginator;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
    }

    /**
     * Order index
     * @Route("/order", name="orders_index", options={"expose" = "true"})
     * @param Request $request
     * @param CurrencyContextInterface $currencyContext
     * @return Response
     */
    public function indexAction(Request $request, CurrencyContextInterface $currencyContext)
    {
        $filter = $request->query->get('filter');
        $status = $request->query->get('status');
        $sort = $request->query->get('order');
        $page = $request->query->getInt('page', 1);

        $queryBuilder = $this->getCollectionQuery($filter, $status, $sort);

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $page,
            self::PAGINATOR_LIMIT
        );

        return $this->render('/admin/order/index.html.twig', [
            'pagination' => $pagination,
            'currency' => $currencyContext->getCurrencyCode(),
        ]);
    }

    /**
     * @param Request $request
     * @param CurrencyContextInterface $currencyContext
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @Route("/order/download.{format}", name="orders_download", options={"expose" = "true"})
     */
    public function downloadAction(Request $request, CurrencyContextInterface $currencyContext)
    {
        $filter = $request->query->get('filter');
        $status = $request->query->get('status');
        $sort = $request->query->get('order');
        $format = $request->get('format');

        $translator = $this->translator;
        $currencySymbol = Currencies::getSymbol($currencyContext->getCurrencyCode());

        $arrayData[] = [
            $translator->trans('app.ui.order.label'),
            $translator->trans('app.ui.name'),
            $translator->trans('app.ui.deliver_date'),
            $translator->trans('app.ui.deliver_time'),
            $translator->trans('app.ui.amount'),
            $translator->trans('app.ui.order_status'),
            $translator->trans('app.ui.rating'),
            $translator->trans('app.ui.comment'),
        ]
        ;
        $name = $translator->trans('app.ui.orders_download');
        /** @var Order[] $orders */
        $orders = $this->getCollectionQuery($filter, $status, $sort)
            ->getQuery()
            ->getResult();

        $writer = null;
        $spreadsheet = new Spreadsheet();

        $spreadsheet
            ->getProperties()
            ->setCreator($this->getParameter('app_name'))
            ->setCompany($this->getParameter('dev_name'))
            ->setCategory($translator->trans('app.ui.order_history'))
            ->setLastModifiedBy($this->getParameter('app_name'))
            ->setTitle($name)
            ->setKeywords("meathouse procasa ordenes pedidos");

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($this->getParameter('app_name') . ' - ' . $this->translator->trans('app.ui.orders_list'));

        /** Set headers styles */
        $styleArray = [
            'font' => [
                'bold' => true,
                'color' => [
                    'rgb' => 'ffffff'
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => [
                    'rgb' => 'ab172c',
                ],
            ],
        ];

        $spreadsheet->getActiveSheet()->getStyle('A1:H1')->applyFromArray($styleArray);

        $colorIndex = 2;
        foreach ($orders as $order) {
            $arrayData[] = [
                '#' . $order->getNumber(),
                $order->getCustomerName() ?? '--',
                $order->getDeliverDate() ?? '--',
                $order->getDeliverTime() ?? '--',
                ($order->getTotal() / 100),
                $translator->trans('app.ui.order.status.' . $order->getStatus()),
                $order->getRating() ? $order->getRating() . '/' . Order::MAX_RATING : '--',
                $order->getRatingComment() ?? '--',
            ];

            $color = '';

            if ($order->getStatus() == Order::STATUS_PENDING) {
                $color = 'd1d1d1';
            }

            if ($order->getStatus() == Order::STATUS_DELIVERED) {
                $color = 'd7ffae';
            }

            if ($order->getStatus() == Order::STATUS_CANCELLED) {
                $color = 'f5b0bd';
            }

            /** Set bold and status color to order number cell */
            $styleArray = [
                'font' => [
                    'bold' => true,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => [
                        'rgb' => $color,
                    ]
                ],
            ];

            $spreadsheet->getActiveSheet()->getStyle('A' . $colorIndex)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('E' . $colorIndex)
                ->getNumberFormat()
                ->setFormatCode('_("'.$currencySymbol.'"* #,##0.00_);_("'.$currencySymbol.'"* \(#,##0.00\);_("'.$currencySymbol.'"* "-"??_);_(@_)');

            $colorIndex++;
        }

        /** Add borders to all table */
        $coordinates = $spreadsheet->getActiveSheet()->fromArray($arrayData, NULL, 'A1')->getCoordinates();
        $start = $coordinates[0];
        $end = end($coordinates);
        $spreadsheet->getActiveSheet()->setAutoFilter($start. ':' . $end);

        $styleArray = [
            'font' => [
                'size' => 15
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $spreadsheet->getActiveSheet()->getStyle($start. ':' . $end)->applyFromArray($styleArray);

        /** Add Autosize to all columns */
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);

        if ($format == 'xlsx') {
            // Create your Office 2007 Excel (XLSX Format)
            $writer = new Xlsx($spreadsheet);
        }

        if ($format == 'csv') {
            // Create your CSV (CSV Format)
            $writer = new Csv($spreadsheet);
        }

        if ($format == 'xls') {
            // Create your Office 97 Excel (XLS Format)
            $writer = new Xls($spreadsheet);
        }

        if ($format == 'html') {
            // Create your HTML (HTML Format)
            $writer = new Html($spreadsheet);
        }

        if ($format == 'pdf') {
            // Create your Pdf file (PDF Format)
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf($spreadsheet);

        }

        // Create a Temporary file in the system
        $fileName = $name . '.' . $format;
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);

        // Create the excel file in the tmp directory of the system
        $writer->save($tempFile);

        // Return the excel file as an attachment
        return $this->file($tempFile, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }

    /**
     * @param Request $request
     * @Route("/order/{id}", name="orders_show", options={"expose" = "true"})
     */
    public function showAction(Request $request)
    {
        $id = $request->get('id');
        $order = $this->entityManager->getRepository('App:Order\Order')->find($id);

        return $this->render('/admin/order/show.html.twig', [
            'order' => $order
        ]);
    }

    /**
     * @param $filter
     * @param $status
     * @param $sort
     * @return QueryBuilder
     */
    private function getCollectionQuery($filter, $status = null, $sort = null)
    {
        $queryBuilder = $this->entityManager->getRepository('App:Order\Order')
            ->createQueryBuilder('o')
            ->leftJoin('o.customer', 'customer')
            ->andWhere('o.state != :cartState')
            ->setParameter('cartState', OrderInterface::STATE_CART);

        /** Text search filter */
        if (!empty($filter)) {
            $queryBuilder
                ->andWhere('o.number LIKE :filter OR o.total LIKE :filter OR customer.email LIKE :filter OR customer.firstName LIKE :filter OR customer.lastName LIKE :filter')
                ->setParameter('filter', '%'.$filter.'%');
        }

        /** Status filter */
        if (!empty($status)) {
            if ($status == Order::STATUS_PENDING) {
                $queryBuilder
                    ->andWhere('o.state = :state')
                    ->setParameter('state', OrderInterface::STATE_NEW);
            }

            if ($status == Order::STATUS_DELIVERED) {
                $queryBuilder
                    ->andWhere('o.shippingState = :shippingState')
                    ->setParameter('shippingState', ShipmentInterface::STATE_SHIPPED);
            }

            if ($status == Order::STATUS_CANCELLED) {
                $queryBuilder
                    ->andWhere('o.state = :state')
                    ->setParameter('state', OrderInterface::STATE_CANCELLED);
            }
        }

        /** Sort filter */
        if (!empty($sort)) {
            if ($sort == Order::SORT_ORDER_NUMBER) {
                $queryBuilder
                    ->orderBy('o.number', 'DESC');
            } else if ($sort == Order::SORT_RECENT) {
                $queryBuilder
                    ->orderBy('o.createdAt', 'DESC');
            } else {
                $queryBuilder
                    ->orderBy('o.createdAt', 'DESC');
            }
        } else {
            $queryBuilder
                ->orderBy('o.createdAt', 'DESC');
        }

        return $queryBuilder;
    }
}
