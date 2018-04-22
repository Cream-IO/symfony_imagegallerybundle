<?php

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use CreamIO\UserBundle\Entity\BUser;
use CreamIO\ImageGalleryBundle\Entity\GalleryCategory;
use CreamIO\ImageGalleryBundle\Entity\GalleryImage;
use Symfony\Component\HttpKernel\KernelInterface;

class FeatureContext extends RawMinkContext
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    use KernelDictionary;

    /**
     * FeatureContext constructor.
     *
     * @param $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @Given the image table is empty
     */
    public function theImageTableIsEmpty(): void
    {
        $em = $this->kernel->getContainer()->get('doctrine')->getManager();
        $em->createQuery('DELETE FROM CreamIOImageGalleryBundle:GalleryImage')->execute();
    }

    /**
     * @Given I load a predictable category in database and get it's id
     */
    public function createPredictableCategory(): int
    {
        $em = $this->kernel->getContainer()->get('doctrine')->getManager();
        $category = new GalleryCategory();
        $category->setTitle('TestCategoryTitle')
            ->setDescription('TestCategoryDesc');
        $em->persist($category);
        $em->flush();

        return $category->getId();
    }

    /**
     * @Given I load a predictable image in database and get it's id
     */
    public function createPredictableImage(): int
    {
        $em = $this->kernel->getContainer()->get('doctrine')->getManager();
        $categoryId = $this->createPredictableCategory();
        $image = new GalleryImage();
        $catrepo = $em->getRepository(GalleryCategory::class);
        $category = $catrepo->find($categoryId);
        $image->setTitle('TestImageTitle')
            ->setDescription('TestImageDescription')
            ->setHtmlAlt('testhtmlalt')
            ->setHtmlTitle('testhtmltitle')
            ->setCategory($category)
            ->setFile('test.png');
        $em->persist($image);
        $em->flush();

        return $image->getId();
    }
}
