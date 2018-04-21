<?php

namespace CreamIO\ImageGalleryBundle\Entity;

use CreamIO\UploadBundle\Model\UserStoredFile as BaseUploadedFile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="CreamIO\ImageGalleryBundle\Repository\GalleryImageRepository")
 * @ORM\Table(name="creamio_gallery_image")
 */
class GalleryImage extends BaseUploadedFile
{
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $htmlAlt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $htmlTitle;

    /**
     * @ORM\ManyToOne(targetEntity="CreamIO\ImageGalleryBundle\Entity\GalleryCategory", inversedBy="images")
     * @ORM\JoinColumn(name="category_id", nullable=false)
     */
    private $category;

    /**
     * @ORM\Column(name="image", type="string")
     *
     * @Assert\NotBlank()
     * @Assert\Image()
     */
    protected $file;

    /**
     * GalleryImage constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->title = '';
        $this->description = '';
        $this->htmlAlt = '';
        $this->htmlTitle = '';
    }

    /**
     * @return null|string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param null|string $title
     *
     * @return GalleryImage
     */
    public function setTitle(?string $title): self
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
     * @param null|string $description
     *
     * @return GalleryImage
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getHtmlAlt(): ?string
    {
        return $this->htmlAlt;
    }

    /**
     * @param string $htmlAlt
     *
     * @return GalleryImage
     */
    public function setHtmlAlt(string $htmlAlt): self
    {
        $this->htmlAlt = $htmlAlt;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getHtmlTitle(): ?string
    {
        return $this->htmlTitle;
    }

    /**
     * @param string $htmlTitle
     *
     * @return GalleryImage
     */
    public function setHtmlTitle(string $htmlTitle): self
    {
        $this->htmlTitle = $htmlTitle;

        return $this;
    }

    /**
     * @return GalleryCategory|null
     */
    public function getCategory(): ?GalleryCategory
    {
        return $this->category;
    }

    /**
     * @param GalleryCategory|null $category
     *
     * @return GalleryImage
     */
    public function setCategory(?GalleryCategory $category): self
    {
        $this->category = $category;

        return $this;
    }
}
