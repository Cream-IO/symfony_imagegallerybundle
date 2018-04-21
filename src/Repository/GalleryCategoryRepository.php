<?php

namespace CreamIO\ImageGalleryBundle\Repository;

use CreamIO\ImageGalleryBundle\Entity\GalleryCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GalleryCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method GalleryCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method GalleryCategory[]    findAll()
 * @method GalleryCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GalleryCategoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GalleryCategory::class);
    }
}
