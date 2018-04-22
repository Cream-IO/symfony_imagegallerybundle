<?php

namespace CreamIO\ImageGalleryBundle\Controller;

use CreamIO\BaseBundle\Exceptions\APIError;
use CreamIO\BaseBundle\Exceptions\APIException;
use CreamIO\BaseBundle\Service\APIService;
use CreamIO\ImageGalleryBundle\Entity\GalleryImage;
use CreamIO\ImageGalleryBundle\Service\GalleryService;
use CreamIO\UploadBundle\Service\UploaderService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class GalleryImageController.
 *
 * @Route("/admin/api/gallery/image")
 */
class GalleryImageController extends Controller
{
    private const ACCEPTED_CONTENT_TYPE_PATCH = 'application/json';
    private const ACCEPTED_CONTENT_TYPE_POST = 'multipart/form-data';
    private const INVALID_POST_CONTENT_TYPE = 'Invalid content type, please send multipart/form-data content';

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
     * @var UploaderService Injected uploader service
     */
    private $uploader;

    /**
     * GalleryImageController constructor.
     *
     * @param APIService         $apiService     Injected API service from base bundle
     * @param GalleryService     $galleryService Injected gallery service
     * @param UploaderService    $uploader       Injected uploader service
     */
    public function __construct(APIService $apiService, GalleryService $galleryService, UploaderService $uploader)
    {
        $this->apiService = $apiService;
        $this->galleryService = $galleryService;
        $this->uploader = $uploader;
        $this->serializer = $galleryService->generateSerializer();
    }

    /**
     * Image upload route.
     *
     * @Route("", name="image_upload", methods="POST")
     *
     * @param Request $request Handled HTTP request
     *
     * @throws \LogicException
     * @throws APIException    If image does not exist
     *
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        if (false === \strpos($request->headers->get('content_type'), self::ACCEPTED_CONTENT_TYPE_POST)) {
            throw $this->apiService->error(Response::HTTP_BAD_REQUEST, self::INVALID_POST_CONTENT_TYPE);
        }
        $category = $this->galleryService->fetchCategoryFromRequest($request);
        /** @var GalleryImage $uploadedFile */
        $uploadedFile = $this->uploader->handleUpload($request, false, GalleryImage::class, 'file');
        $uploadedFile->setCategory($category);
        $this->apiService->validateEntity($uploadedFile);
        $em = $this->getDoctrine()->getManager();
        $em->persist($uploadedFile);
        $em->flush();

        return $this->apiService->successWithoutResults($uploadedFile->getId(), Response::HTTP_OK, $request);
    }

    /**
     * Image deletion route.
     *
     * @Route("/{id}", name="image_delete", methods="DELETE")
     *
     * @param Request $request The handled HTTP request
     * @param int     $id      Image id to delete
     *
     * @throws \LogicException
     * @throws APIException    If image does not exist
     *
     * @return JsonResponse
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $image = $em->getRepository(GalleryImage::class)->find($id);
        if (null === $image) {
            throw $this->apiService->error(Response::HTTP_NOT_FOUND, APIError::RESOURCE_NOT_FOUND);
        }
        $em->remove($image);
        $em->flush();

        return $this->apiService->successWithoutResults($id, Response::HTTP_OK, $request);
    }

    /**
     * Image details route.
     *
     * @Route("/{id}", name="image_get", methods="GET")
     *
     * @param Request $request Handled HTTP request
     * @param int     $id      Image id to get
     *
     * @throws APIException    If id does not exists
     * @throws \LogicException
     *
     * @return JsonResponse
     */
    public function details(Request $request, int $id): JsonResponse
    {
        $image = $this->getDoctrine()->getManager()->getRepository(GalleryImage::class)->find($id);
        if (null === $image) {
            throw $this->apiService->error(Response::HTTP_NOT_FOUND, APIError::RESOURCE_NOT_FOUND);
        }

        return $this->apiService->successWithResults(['gallery-image' => $image], Response::HTTP_OK, $image->getId(), $request, $this->serializer);
    }

    /**
     * Image patch route.
     *
     * @Route("/{id}", name="image_patch", methods="PATCH")
     *
     * @param Request $request Handled HTTP request
     * @param int     $id      Image id to patch
     *
     * @throws \LogicException
     * @throws APIException
     *
     * @return JsonResponse
     */
    public function patch(Request $request, int $id): JsonResponse
    {
        if (self::ACCEPTED_CONTENT_TYPE_PATCH !== $request->headers->get('content_type')) {
            throw $this->apiService->error(Response::HTTP_BAD_REQUEST, APIError::INVALID_CONTENT_TYPE);
        }
        $image = $this->getDoctrine()->getManager()->getRepository(GalleryImage::class)->find($id);
        if (null === $image) {
            throw $this->apiService->error(Response::HTTP_NOT_FOUND, APIError::RESOURCE_NOT_FOUND);
        }
        $datas = $request->getContent();
        $image = $this->galleryService->mergeImageFromJSON($image, $datas);
        $this->apiService->validateEntity($image);
        $this->getDoctrine()->getManager()->flush();

        return $this->apiService->successWithoutResults($id, Response::HTTP_OK, $request);
    }
}
