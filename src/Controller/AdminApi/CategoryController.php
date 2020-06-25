<?php

namespace App\Controller\AdminApi;

use App\Entity\Locale\Locale;
use App\Entity\Taxonomy\Taxon;
use Doctrine\ORM\QueryBuilder;
use App\Form\AdminApi\CategoryType;
use App\Pagination\PaginationFactory;
use Doctrine\ORM\EntityManagerInterface;
use Hshn\Base64EncodedFile\HttpFoundation\File\Base64EncodedFile;
use Hshn\Base64EncodedFile\HttpFoundation\File\UploadedBase64EncodedFile;
use Liip\ImagineBundle\Service\FilterService;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Core\Uploader\ImageUploaderInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sylius\Component\Core\Model\TaxonInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sylius\Bundle\TaxonomyBundle\Doctrine\ORM\TaxonRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * CategoryController
 * @Route("/categories")
 */
class CategoryController extends AbstractFOSRestController
{
    const ITEMS_PER_PAGE = 10;

    const ORIGINAL_IMAGE_KEY = 'shop_api_product_original';

    /** @var TaxonRepository */
    private $categoryRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @var FilterService
     */
    private $filterService;

    /** @var ImageUploaderInterface */
    private $imageUploader;

    /** @var FactoryInterface */
    private $taxonImageFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CategoryController constructor.
     * @param TaxonRepository $categoryRepository
     * @param EntityManagerInterface $entityManager
     * @param FilterService $filterService
     * @param ImageUploaderInterface $imageUploader
     * @param FactoryInterface $taxonImageFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        TaxonRepository $categoryRepository,
        EntityManagerInterface $entityManager,
        FilterService $filterService,
        ImageUploaderInterface $imageUploader,
        FactoryInterface $taxonImageFactory,
        LoggerInterface $logger
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->entityManager = $entityManager;
        $this->filterService = $filterService;
        $this->imageUploader = $imageUploader;
        $this->taxonImageFactory = $taxonImageFactory;
        $this->logger = $logger;
    }

    /**
     * @Route(
     *     ".{_format}",
     *     name="admin_api_categories_index",
     *     methods={"GET"}
     * )
     *
     * @param Request $request
     * @param PaginationFactory $paginationFactory
     * @return Response
     */
    public function indexAction(Request $request, PaginationFactory $paginationFactory)
    {
        $statusCode = Response::HTTP_OK;
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', self::ITEMS_PER_PAGE);
        $search = $request->query->get('search');

        $queryBuilder = $this->getQueryBuilder($search);
        $paginatedCollection = $paginationFactory->createCollection($queryBuilder, $search, $page, $limit, 'admin_api_categories_index', [], 'Category list.', $statusCode, 'info');

        $list = [];
        foreach ($paginatedCollection->recordset as $category) {
            $list[] = $this->serializeCategory($category);
        }
        $paginatedCollection->recordset = $list;

        $view = $this->view($paginatedCollection, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/{code}.{_format}",
     *     name="admin_api_categories_show",
     *     methods={"GET"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function showAction(Request $request)
    {
        $category = $this->getCategory($request);
        $view = $this->view($this->serializeCategory($category), Response::HTTP_OK);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     ".{_format}",
     *     name="admin_api_categories_new",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(CategoryType::class, null, ['validation_groups' => ['creation']]);
        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $code = $form->get('code')->getData();
            $name = $form->get('name')->getData();
            $description = $form->get('description')->getData();
            $parent = $form->get('parent')->getData();
            $photo = $form->get('photo')->getData();
            $left = $form->get('left')->getData();
            $right = $form->get('right')->getData();
            $position = $form->get('position')->getData();

            $category = $this->createCategory($code);
            $category = $this->updateCategory($category, $name, $description, $parent, $photo, $left, $right, $position);

            $this->entityManager->persist($category);
            $this->entityManager->flush();

            $view = $this->view($this->serializeCategory($category), Response::HTTP_OK);

            return $this->handleView($view);
        }

        $statusCode = Response::HTTP_BAD_REQUEST;
        $view = $this->view(['type' => 'error', 'message' => 'Invalid form.', 'recordset' => $form->getErrors(), 'code' => $statusCode], $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/{code}.{_format}",
     *     name="admin_api_categories_edit",
     *     methods={"PUT"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function editAction(Request $request)
    {
        $category = $this->getCategory($request);

        $form = $this->createForm(CategoryType::class, null, ['code' => $category->getCode()]);
        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $name = $form->get('name')->getData();
            $description = $form->get('description')->getData();
            $parent = $form->get('parent')->getData();
            $photo = $form->get('photo')->getData();
            $left = $form->get('left')->getData();
            $right = $form->get('right')->getData();
            $position = $form->get('position')->getData();

            $category = $this->updateCategory($category, $name, $description, $parent, $photo, $left, $right, $position);

            $this->entityManager->persist($category);
            $this->entityManager->flush();

            $view = $this->view($this->serializeCategory($category), Response::HTTP_OK);

            return $this->handleView($view);
        }

        $statusCode = Response::HTTP_BAD_REQUEST;
        $view = $this->view(['type' => 'error', 'message' => 'Invalid form.', 'recordset' => $form->getErrors(), 'code' => $statusCode], $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/{code}.{_format}",
     *     name="admin_api_categories_delete",
     *     methods={"DELETE"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function deleteAction(Request $request)
    {
        $category = $this->getCategory($request);
        $this->entityManager->remove($category);

        try {
            $this->entityManager->flush();
            return new JsonResponse([], Response::HTTP_NO_CONTENT);
        } catch (\Exception $exception) {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $view = $this->view(['type' => 'error', 'message' => 'Invalid form.', 'recordset' => $exception->getMessage(), 'code' => $statusCode], $statusCode);

            return $this->handleView($view);
        }
    }

    /**
     * @param $search
     * @return QueryBuilder
     */
    private function getQueryBuilder($search): QueryBuilder
    {
        $queryBuilder = $this->categoryRepository
            ->createQueryBuilder('taxon')
            ->leftJoin('taxon.translations', 'translations')
            ->andWhere('translations.locale = :locale')
            ->setParameter('locale', Locale::DEFAULT_LOCALE)
            ->orderBy('taxon.position', 'ASC');

        if (!empty($search)) {
            $queryBuilder
                ->andWhere('taxon.code LIKE :search OR translations.name LIKE :search OR translations.description LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        return $queryBuilder;
    }

    /**
     * @param TaxonInterface|null $category
     * @return array
     */
    private function serializeCategory(?TaxonInterface $category): array
    {
        if (null == $category) {
            return [];
        }

        $photoURL = null;

        if ($category->getImages()[0] ?? null) {
            try {
                $photoURL = $this->filterService->getUrlOfFilteredImage($category->getImages()[0]->getPath(), self::ORIGINAL_IMAGE_KEY);
            } catch (\Exception $exception) {
                $this->logger->error($exception->getMessage());
            }
        }

        return [
            'id' => $category->getId(),
            'code' => $category->getCode(),
            'name' => $category->getName(),
            'parent' => $this->serializeCategory($category->getParent()),
            'left' => $category->getLeft(),
            'right' => $category->getRight(),
            'level' => $category->getLevel(),
            'position' => $category->getPosition(),
            'photo' => $photoURL,
            'created_at' => $category->getCreatedAt(),
        ];
    }

    /**
     * @param Request $request
     * @return Taxon|null
     */
    private function getCategory(Request $request): ?Taxon
    {
        $code = $request->get('code');
        /** @var Taxon $category */
        $category = $this->categoryRepository->findOneBy(['code' => $code]);

        if (!$category instanceof Taxon) {
            throw new NotFoundHttpException('Category not found.');
        }

        return $category;
    }

    /**
     * @param $id
     * @return Taxon|null
     */
    private function getCategoryById($id): ?Taxon
    {
        /** @var Taxon $category */
        $category = $this->categoryRepository->find($id);

        return $category;
    }

    /**
     * @param $code
     * @return Taxon
     */
    private function createCategory($code): Taxon
    {
        $category = $this->categoryRepository->findOneBy(['code' => $code]);

        if ($category instanceof Taxon) {
            throw new BadRequestHttpException('Category already exists');
        }

        $category = new Taxon();
        $category->setCurrentLocale(Locale::DEFAULT_LOCALE);
        $category->setCode($code);
        $category->setSlug($code);

        return $category;
    }

    /**
     * @param Taxon $category
     * @param $name
     * @param $description
     * @param $parentId
     * @param $base64Content
     * @param $left
     * @param $right
     * @param $position
     * @return Taxon
     */
    private function updateCategory(Taxon $category, $name, $description, $parentId, $base64Content, $left, $right, $position): Taxon
    {
        $category->setCurrentLocale(Locale::DEFAULT_LOCALE);

        if (!empty($name)) {
            $category->setName($name);
        }

        if (!empty($description)) {
            $category->setDescription($description);
        }

        if (!empty($left)) {
            $category->setLeft($left);
        }

        if (!empty($right)) {
            $category->setRight($right);
        }

        if (!empty($position)) {
            $category->setPosition($position);
        }

        if (!empty($base64Content)) {
            foreach ($category->getImages() as $image) {
                $this->entityManager->remove($image);
            }

            /** @var ImageInterface $taxonImage */
            $taxonImage = $this->taxonImageFactory->createNew();
            $taxonImage->setFile(new UploadedBase64EncodedFile(new Base64EncodedFile($base64Content)));

            $this->entityManager->persist($taxonImage);
            $this->imageUploader->upload($taxonImage);

            $category->addImage($taxonImage);
        }

        if ($parentId) {
            $parent = $this->getCategoryById($parentId);

            if ($parent instanceof Taxon) {
                $category->setParent($parent);
            }
        } else {
            $category->setParent(null);
        }

        return $category;
    }
}
