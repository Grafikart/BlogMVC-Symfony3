<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Comment;
use AppBundle\Entity\Post;
use AppBundle\Entity\User;
use AppBundle\Form\CommentType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Post controller.
 *
 */
class PostController extends Controller
{
    /**
     * Lists all post entities.
     *
     * @Route("/", name="root")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getRepository('AppBundle:Post')->createQueryBuilderWithCategory()
            ->getQuery();

        $posts = $this->get('knp_paginator')->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('post/index.html.twig', array(
            'posts' => $posts,
            'page' => $request->query->getInt('page', 1)));
    }

    /**
     * Finds and displays a post entity.
     *
     * @Route("/post/{slug}", name="post_show")
     * @Method({"GET", "POST"})
     */
    public function showAction(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository("AppBundle:Post")->createQueryBuilderWithUserAndCategory()
            ->where('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getSingleResult();

        $comment = new Comment();
        $comment->setPost($post);

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setPost($post);
            $em->persist($comment);
            $em->flush();
            $this->addFlash('success', 'Thanks for your comment');
            return $this->redirectToRoute('post_show', ['slug' => $post->getSlug()]);
        }

        return $this->render('post/show.html.twig', [
            'comment_form' => $form->createView(),
            'post' => $post
        ]);
    }

    /**
     * @Route("/author/{id}", name="post_author")
     * @Method("GET")
     */
    public function authorAction(Request $request, User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getRepository('AppBundle:Post')->createQueryBuilderWithCategory()
            ->orderBy("p.createdAt", "DESC")
            ->getQuery();

        $posts = $this->get('knp_paginator')->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('post/index.html.twig', [
            'author' => $user,
            'posts' => $posts,
            'page' => $request->query->getInt('page', 1)
        ]);
    }

    /**
     * @Route("/category/{slug}", name="post_category")
     * @Method("GET")
     */
    public function categoryAction(Request $request, Category $category)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getRepository('AppBundle:Post')->createQueryBuilderWithCategory($category)
            ->orderBy("p.createdAt", "DESC")
            ->getQuery();

        $posts = $this->get('knp_paginator')->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('post/index.html.twig', [
            'category' => $category,
            'posts' => $posts,
            'page' => $request->query->getInt('page', 1)
        ]);
    }

    public function sidebarAction()
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository('AppBundle:Category')->findAll();
        $posts = $em->getRepository('AppBundle:Post')->findBy(
            [],
            ['createdAt' => 'DESC'],
            2,
            0
        );
        return $this->render('partials/sidebar.html.twig', [
            'categories' => $categories,
            'posts' => $posts
        ]);
    }

}
