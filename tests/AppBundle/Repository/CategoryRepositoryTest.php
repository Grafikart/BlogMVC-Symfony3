<?php

namespace Tests\AppBundle\Repository;

use AppBundle\Entity\Category;
use AppBundle\Entity\Post;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CategoryRepositoryTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * {@inheritDoc}
     */
    protected function setUp(){
        self::bootKernel();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->createSchema();
        parent::setUp();
    }

    protected function createCategory($i = 1) {
        $category = new Category();
        $category->setName("Category $i");
        $category->setSlug("category-$i");
        return $category;
    }

    protected function createPost($i = 1) {
        $post = new Post();
        $post->setName("Post $i");
        $post->setSlug("post-$i");
        $post->setContent("some fake content");
        return $post;
    }

    public function testCategoryPostCountonCreate(){
        $category = $this->createCategory();
        $category2 = $this->createCategory(2);
        $this->em->persist($category);
        $this->em->persist($category2);
        $this->em->flush();
        $this->em->refresh($category);
        $this->assertEquals(0, $category->getPostCount());

        $post = $this->createPost();
        $post->setCategory($category);
        $this->em->persist($post);
        $this->em->flush();
        $this->em->refresh($category);
        $this->assertEquals(1, $category->getPostCount());
    }

    public function testCategoryPostCountOnDelete(){
        $category = $this->createCategory();
        $this->em->persist($category);
        $post = $this->createPost();
        $post->setCategory($category);
        $this->em->persist($post);
        $this->em->flush();
        $this->em->refresh($category);
        $this->assertEquals(1, $category->getPostCount());
        // We delete the post
        $this->em->remove($post);
        $this->em->flush();
        $this->em->refresh($category);
        $this->assertEquals(0, $category->getPostCount());
    }

    public function testCategoryPostCountonUpdate(){
        $category = $this->createCategory();
        $category2 = $this->createCategory(2);
        $this->em->persist($category);
        $this->em->persist($category2);
        $post = $this->createPost();
        $post->setCategory($category);
        $this->em->persist($post);
        $this->em->flush();
        $this->em->refresh($category);
        $this->em->refresh($category2);
        // We test the starting point
        $this->assertEquals(1, $category->getPostCount());
        $this->assertEquals(0, $category2->getPostCount());
        // We update the post
        $post->setCategory($category2);
        $this->em->persist($post);
        $this->em->flush();
        $this->em->refresh($category);
        $this->em->refresh($category2);
        $this->assertEquals(0, $category->getPostCount());
        $this->assertEquals(1, $category2->getPostCount());

    }

    protected function tearDown()
    {
        parent::tearDown();
        // $this->dropSchema();
        $this->em->close();
        $this->em = null; // Avoid memory leaks
    }

    protected function createSchema()
    {
        // Get the metadata of the application to create the schema.
        $metadata = $this->getMetadata();

        if ( ! empty($metadata)) {
            // Create SchemaTool
            $tool = new SchemaTool($this->em);
            $tool->createSchema($metadata);
        } else {
            throw new SchemaException('No Metadata Classes to process.');
        }
    }

    protected function dropSchema()
    {
        // Get the metadata of the application to create the schema.
        $metadata = $this->getMetadata();

        if ( ! empty($metadata)) {
            // Create SchemaTool
            $tool = new SchemaTool($this->em);
            $tool->dropSchema($metadata);
        } else {
            throw new SchemaException('No Metadata Classes to process.');
        }
    }

    protected function getMetadata()
    {
        return $this->em->getMetadataFactory()->getAllMetadata();
    }

}
