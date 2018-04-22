<?php

namespace CreamIO\ImageGalleryBundle\Controller;

use CreamIO\BaseBundle\Exceptions\APIError;
use CreamIO\BaseBundle\Exceptions\APIException;
use CreamIO\BaseBundle\Service\APIService;
use CreamIO\ImageGalleryBundle\Entity\GalleryCategory;
use CreamIO\ImageGalleryBundle\Service\GalleryService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;

/**
 * Class GalleryCategoryController.
 *
 * @Route("/admin/api/gallery/category", name="creamio_api_")
 */
class GalleryCategoryController extends Controller
{
    private const LIST_RESULTS_FOR_IDENTIFIER = 'gallery-categories-list';
    private const ACCEPTED_CONTENT_TYPE = 'application/json';

    /**
     * @var APIService Injected API service from base bundle
     */
    private $apiService;

    /**
     * @var GalleryService Injected gallery service
     */
    private $galleryService;

    /**
     * @var Serializer Generated serializer
     */
    private $serializer;

    /**
     * GalleryCategoryController constructor.
     *
     * @param APIService         $apiService     Injected API service from base bundle
     * @param GalleryService     $galleryService Inject gallery service
     */
    public function __construct(APIService $apiService, GalleryService $galleryService)
    {
        $this->apiService = $apiService;
        $this->galleryService = $galleryService;
        $this->serializer = $galleryService->generateSerializer();
    }

    /**
     * @Route("", name="category_post", methods="POST")
     *
     * @param Request $request Handled HTTP request
     *
     * @throws APIException    If validation fails
     * @throws \LogicException
     *
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        if (self::ACCEPTED_CONTENT_TYPE !== $request->headers->get('content_type')) {
            throw $this->apiService->error(Response::HTTP_BAD_REQUEST, APIError::INVALID_CONTENT_TYPE);
        }
        $datas = $request->getContent();
        /** @var GalleryCategory $category */
        $category = $this->galleryService->generateSerializer()->deserialize($datas, GalleryCategory::class, 'json');
        $this->apiService->validateEntity($category);
        $em = $this->getDoctrine()->getManager();
        $em->persist($category);
        $em->flush();
        $redirectionUrl = $this->generateUrl('cream_io_image_gallery.image.details', ['id' => $category->getId()]);

        return $this->apiService->successWithoutResultsRedirected($category->getId(), $request, Response::HTTP_CREATED, $redirectionUrl);
    }

    /**
     * Category details list route.
     *
     * @Route("", name="category_list_get", methods="GET")
     *
     * @param Request $request Handled HTTP request
     *
     * @throws \LogicException
     *
     * @return JsonResponse
     */
    public function detailsList(Request $request): JsonResponse
    {
        $categoriesList = $this->galleryService->formatCategoriesList();

        return $this->apiService->successWithResults(['gallery-categories' => $categoriesList], Response::HTTP_OK, self::LIST_RESULTS_FOR_IDENTIFIER, $request, $this->serializer);
    }

    /**
     * Category details route.
     *
     * @Route("/{id}", name="category_get", methods="GET")
     *
     * @param Request $request Handled HTTP request
     * @param int     $id      Category id to get
     *
     * @throws APIException    If id does not exists
     * @throws \LogicException
     *
     * @return JsonResponse
     */
    public function details(Request $request, int $id): JsonResponse
    {
        $category = $this->getDoctrine()->getManager()->getRepository(GalleryCategory::class)->find($id);
        if (null === $category) {
            throw $this->apiService->error(Response::HTTP_NOT_FOUND, APIError::RESOURCE_NOT_FOUND);
        }

        return $this->apiService->successWithResults(['gallery-category' => $category], Response::HTTP_OK, $category->getId(), $request, $this->serializer);
    }

    /**
     * Category deletion route.
     *
     * @Route("/{id}", name="category_delete", methods="DELETE")
     *
     * @param Request $request The handled HTTP request
     * @param int     $id      Category id to delete
     *
     * @throws \LogicException
     * @throws APIException    If category does not exist
     *
     * @return JsonResponse
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        /** @var GalleryCategory $category */
        $category = $em->getRepository(GalleryCategory::class)->find($id);
        if (null === $category) {
            throw $this->apiService->error(Response::HTTP_NOT_FOUND, APIError::RESOURCE_NOT_FOUND);
        }
        foreach ($category->getImages() as $image) {
            $em->remove($image);
        }
        $em->remove($category);
        $em->flush();

        return $this->apiService->successWithoutResults($id, Response::HTTP_OK, $request);
    }

    /**
     * Category patch route.
     *
     * @Route("/{id}", name="category_patch", methods="PATCH")
     *
     * @param Request $request Handled HTTP request
     * @param int     $id      Category id to patch
     *
     * @throws \LogicException
     * @throws APIException
     *
     * @return JsonResponse
     */
    public function patch(Request $request, int $id): JsonResponse
    {
        if (self::ACCEPTED_CONTENT_TYPE !== $request->headers->get('content_type')) {
            throw $this->apiService->error(Response::HTTP_BAD_REQUEST, APIError::INVALID_CONTENT_TYPE);
        }
        $category = $this->getDoctrine()->getManager()->getRepository(GalleryCategory::class)->find($id);
        if (null === $category) {
            throw $this->apiService->error(Response::HTTP_NOT_FOUND, APIError::RESOURCE_NOT_FOUND);
        }
        $datas = $request->getContent();
        $category = $this->galleryService->mergeCategoryFromJSON($category, $datas);
        $this->apiService->validateEntity($category);
        $this->getDoctrine()->getManager()->flush();

        return $this->apiService->successWithoutResults($id, Response::HTTP_OK, $request);
    }
}
