<?php

namespace CreamIO\ImageGalleryBundle\Service;

use CreamIO\BaseBundle\Exceptions\APIException;
use CreamIO\BaseBundle\Service\APIService;
use CreamIO\ImageGalleryBundle\Entity\GalleryCategory;
use CreamIO\ImageGalleryBundle\Entity\GalleryImage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Class GalleryService.
 */
class GalleryService
{
    private const WRONG_CATEGORY_ID_ERROR = 'The provided category-id does not exist.';
    private const NO_CATEGORY_ID_PROVIDED_ERROR = 'You did not provide a category-id.';
    private const CANT_PROVIDE_CATEGORY_ERROR = 'You can\'t send directly a category, you must use category-id instead.';

    /**
     * @var EntityManagerInterface Injected doctrine service
     */
    private $em;

    /**
     * @var APIService Injected API service
     */
    private $apiService;

    /**
     * GalleryService constructor.
     *
     * @param EntityManagerInterface $entityManager Injected doctrine service
     * @param APIService             $apiService    Injected API service from base bundle
     */
    public function __construct(EntityManagerInterface $entityManager, APIService $apiService)
    {
        $this->em = $entityManager;
        $this->apiService = $apiService;
    }

    /**
     * Serializer generator.
     *
     * Allows to generate a serializer with circular reference handling
     *
     * @return Serializer Serializer instance
     */
    public function generateSerializer(): Serializer
    {
        $encoders = [new JsonEncoder()];
        $objectNormalizer = new ObjectNormalizer();
        $objectNormalizer->setCircularReferenceLimit(1);
        $objectNormalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
        $normalizers = [new DateTimeNormalizer('d-m-Y H:i:s', new \DateTimeZone('Europe/Paris')), $objectNormalizer];

        return new Serializer($normalizers, $encoders);
    }

    /**
     * Gets all categories from database, and return it formatted in a array where the key is the id of the category
     *
     * @return array
     */
    public function formatCategoriesList(): array
    {
        $repo = $this->em->getRepository(GalleryCategory::class);
        /** @var GalleryCategory[] $categoriesList */
        $categoriesList = $repo->findAll();
        $finalCategoriesList = [];
        foreach ($categoriesList as $category) {
            $finalCategoriesList[$category->getId()] = $category;
        }

        return $finalCategoriesList;
    }

    /**
     * Takes parameter bag with datas comming from client to post image and finds the category form the given category id
     *
     * @param ParameterBag $paramBag Datas comming from client request
     *
     * @return GalleryCategory
     */
    private function getCategoryFromClientDatasPost(ParameterBag $paramBag): GalleryCategory
    {
        if (false === $paramBag->has('category-id')) {
            throw $this->apiService->error(Response::HTTP_BAD_REQUEST, self::NO_CATEGORY_ID_PROVIDED_ERROR);
        }
        $categoryRepo = $this->em->getRepository(GalleryCategory::class);
        $category = $categoryRepo->find($paramBag->get('category-id'));
        if (null === $category) {
            throw $this->apiService->error(Response::HTTP_BAD_REQUEST, self::WRONG_CATEGORY_ID_ERROR);
        }
        $paramBag->remove('category-id');

        return $category;
    }

    /**
     * Turns a category id into a category entity from the request, and removes it from the request parameter bag.
     *
     * @param Request $request
     *
     * @throws APIException If missing category-id or category doesn't exist or client sent category directly
     *
     * @return GalleryCategory
     */
    public function fetchCategoryFromRequest(Request $request): GalleryCategory
    {
        if (true === $request->request->has('category')) {
            throw $this->apiService->error(Response::HTTP_BAD_REQUEST, self::CANT_PROVIDE_CATEGORY_ERROR);
        }
        $category = $this->getCategoryFromClientDatasPost($request->request);

        return $category;
    }

    /**
     * Merge existing gallery category with new values incomming from client.
     *
     * @param GalleryCategory $category Category to update
     * @param string          $datas    JSON string from client
     *
     * @return GalleryCategory Merged category
     */
    public function mergeCategoryFromJSON(GalleryCategory $category, string $datas): GalleryCategory
    {
        $serializer = $this->generateSerializer();
        /** @var GalleryCategory $category */
        $category = $serializer->deserialize($datas, GalleryCategory::class, 'json', ['object_to_populate' => $category]);

        return $category;
    }

    /**
     * Takes array with datas comming from client to patch image and finds the category form the given category id
     *
     * @param array $datas Datas comming from client
     *
     * @return GalleryCategory
     */
    private function getCategoryFromClientDatasPatch(array &$datas): GalleryCategory
    {
        $categoryId = $datas['category-id'];
        $categoryRepo = $this->em->getRepository(GalleryCategory::class);
        $category = $categoryRepo->find($categoryId);
        if (null === $category) {
            throw $this->apiService->error(Response::HTTP_BAD_REQUEST, self::WRONG_CATEGORY_ID_ERROR);
        }
        unset($datas['category-id']);

        return $category;
    }

    /**
     * Merge existing gallery image with new values incomming from client.
     *
     * @param GalleryImage $image Category to update
     * @param string       $datas JSON string from client
     *
     * @throws APIException If missing category-id or category doesn't exist or client sent category directly
     *
     * @return GalleryImage Merged image
     */
    public function mergeImageFromJSON(GalleryImage $image, string $datas): GalleryImage
    {
        $datasArray = \json_decode($datas, true);
        if (true === \array_key_exists('category', $datasArray)) {
            throw $this->apiService->error(Response::HTTP_BAD_REQUEST, self::CANT_PROVIDE_CATEGORY_ERROR);
        }
        if (true === \array_key_exists('category-id', $datasArray)) {
            $datasArray['category'] = $this->getCategoryFromClientDatasPatch($datasArray);
        }
        $serializer = $this->generateSerializer();
        $image = $serializer->denormalize($datasArray, GalleryImage::class, null, ['object_to_populate' => $image]);

        return $image;
    }
}
