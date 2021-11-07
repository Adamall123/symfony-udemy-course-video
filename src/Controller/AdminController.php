<?php

namespace App\Controller;

use App\Entity\Category;
use App\Utils\CategoryTreeAdminList;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/**
     * @Route("/admin")
     */
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="admin_main_page")
     */
    public function index(): Response
    {
        return $this->render('admin/my_profile.html.twig');
    }

    /**
     * @Route("/categories", name="categories")
     */
    public function categories(CategoryTreeAdminList $categories): Response
    {
        
        $categories->getCategoryList($categories->buildTree());
        //dump($categories->categoryList); exit;
        return $this->render('admin/categories.html.twig',[
            'categories' => $categories->categoryList
        ]);
    }
    /**
     * @Route("/upload_video", name="upload_video")
     */
    public function upload_video(): Response
    {
        return $this->render('admin/upload_video.html.twig');
    }
    /**
     * @Route("/users", name="users")
     */
    public function users(): Response
    {
        return $this->render('admin/users.html.twig');
    }
    /**
     * @Route("/videos", name="videos")
     */
    public function videos(): Response
    {
        return $this->render('admin/videos.html.twig');
    }
    /**
     * @Route("/edit-category", name="edit_category")
     */
    public function editCategroy(): Response
    {
        return $this->render('admin/edit_category.html.twig');
    }
    /**
     * @Route("/delete-category/{id}", name="delete_category")
     */
    public function deleteCategory(Category $category) //automatically find object by id 
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($category);
        $entityManager->flush();
        return $this->redirectToRoute('categories');
    }
}
