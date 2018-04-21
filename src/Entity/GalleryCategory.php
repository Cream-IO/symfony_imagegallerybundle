<?php

namespace CreamIO\ImageGalleryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="CreamIO\ImageGalleryBundle\Repository\GalleryCategoryRepository")
 * @ORM\Table(name="creamio_gallery_category")
 */
class GalleryCategory
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="CreamIO\ImageGalleryBundle\Entity\GalleryImage", mappedBy="category")
     */
    private $images;

    /**
     * GalleryCategory constructor.
     */
    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->description = '';
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return GalleryCategory
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return GalleryCategory
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|GalleryImage[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    /**
     * @param GalleryImage $image
     *
     * @return GalleryCategory
     */
    public function addImage(GalleryImage $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setCategory($this);
        }

        return $this;
    }

    /**
     * @param GalleryImage $image
     *
     * @return GalleryCategory
     */
    public function removeImage(GalleryImage $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            if ($image->getCategory() === $this) {
                $image->setCategory(null);
            }
        }

        return $this;
    }
}
