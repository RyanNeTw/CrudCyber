<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Blog;
use App\Repository\BlogRepository;

class BlogController extends AbstractController
{
    #[Route('/', name: 'app_blog')]
    public function index(BlogRepository $blogRepository): Response
    {
        $blogs = $blogRepository->findAll();
        return $this->render('blog/index.html.twig', [
            'blogs' => $blogs
        ]);
    }

    #[Route('/blog/create', name: 'create_blog')]
    public function createBlog(Request $request, EntityManagerInterface $entityManager): Response
    {
        $blog = new Blog();
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $blog->setName($name);
            $blog->setAuthor($request->request->get('author'));
            $blog->setRate($request->request->get('rate'));
            $blog->setSlug($this->generateSlug($name));

            $entityManager->persist($blog);
            $entityManager->flush();

            return $this->redirectToRoute('app_blog');
        }

        return $this->render('blog/create.html.twig');
    }

    #[Route('/blog/delete/{slug}', name: 'delete_blog')]
    public function deleteBlog(string $slug, EntityManagerInterface $entityManager): Response
    {
        $blog = $entityManager->getRepository(Blog::class)->findOneBy(['slug' => $slug]);

        if (!$blog) {
            throw $this->createNotFoundException(
                'Aucun blog trouvé pour cet slug : '.$slug
            );
        }

        $entityManager->remove($blog);
        $entityManager->flush();

        return $this->redirectToRoute('app_blog');
    }

    #[Route('/blog/update/{slug}', name: 'update_blog')]
    public function updateBlog(Request $request, string $slug, EntityManagerInterface $entityManager): Response
    {
        $blog = $entityManager->getRepository(Blog::class)->findOneBy(['slug' => $slug]);

        if (!$blog) {
            throw $this->createNotFoundException('Aucun blog trouvé pour cet slug : '.$slug);
        }

        if ($request->isMethod('POST')) {
            $blog->setName($request->request->get('name'));
            $blog->setAuthor($request->request->get('author'));
            $blog->setRate($request->request->get('rate'));

            $entityManager->flush();

            return $this->redirectToRoute('app_blog');
        }

        return $this->render('blog/update.html.twig', [
            'blog' => $blog,
        ]);
    }

    private function generateSlug(string $name): string
    {
        $slug = 'slug-' .  strtolower(trim($name));
        return $slug;
    }
}
