<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\User;
use App\Form\CategoryType;
use App\Utils\CategoryTreeAdminList;
use App\Utils\CategoryTreeAdminOptionList;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Form\UserType;
/**
     * @Route("/admin")
     */
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="admin_main_page")
     */
    public function index(Request $request): Response
    {
        $user = $this->getUser(); 
        $form = $this->createForm(UserType::class, $user,['user'=>$user]);
        $form->handleRequest($request);
        $is_invalid = null; 
        if($form->isSubmitted() && $form->isValid())
        {
           $this->addFlash(
               'success',
               'Your changes were saved'
           );
           return $this->redirectToRoute('admin_main_page');
        }

        return $this->render('admin/my_profile.html.twig',[
            'subscription' => $this->getUser()->getSubscription(),
            'form' => $form->createView(),
            'is_invalid' => $is_invalid
        ]);
    }
    /**
     * @Route("/cancel-plan", name="cancel_plan")
     */
    public function cancelPlan()
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser());

        $subscription = $user->getSubscription();
        $subscription->setValidTo(new \Datetime());
        $subscription->setPaymentStatus(null);
        $subscription->setPlan('canceled');

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->persist($subscription);
        $entityManager->flush();

        return $this->redirectToRoute('admin_main_page');
    }
    /**
     * @Route("/su/categories", name="categories", methods={"GET","POST"})
     */
    public function categories(CategoryTreeAdminList $categories, Request $request): Response
    {
        //$request object is for handlign data with get and post 
        $categories->getCategoryList($categories->buildTree());

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $is_invalid = null;
       
       
        if($this->saveCategory($category, $form, $request))
        {

            return $this->redirectToRoute('categories');
        }
        elseif($request->isMethod('post'))
        {
            $is_invalid = ' is-invalid';
        }
        //dump($categories->categoryList); exit;
        return $this->render('admin/categories.html.twig',[
            'categories' => $categories->categoryList,
            'form' => $form->createView(),
            'is_invalid' =>  $is_invalid
        ]);
    }
    /**
     * @Route("/su/upload_video", name="upload_video")
     */
    public function upload_video(): Response
    {
        return $this->render('admin/upload_video.html.twig');
    }
    /**
     * @Route("/su/users", name="users")
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
     * @Route("/su/edit-category/{id}", name="edit_category", methods={"GET","POST"})
     */
    public function editCategroy(Category $category, Request $request): Response //param converter symfony 
    {
        $form = $this->createForm(CategoryType::class, $category);
        $is_invalid = null;
        if($this->saveCategory($category, $form, $request))
        {

            return $this->redirectToRoute('categories');
        }
        elseif($request->isMethod('post'))
        {
            $is_invalid = ' is-invalid';
        }
        return $this->render('admin/edit_category.html.twig',[
            'category' => $category,
            'form' => $form->createView(),
            'is_invalid' => $is_invalid
        ]);
    }
    /**
     * @Route("/su/delete-category/{id}", name="delete_category")
     */
    public function deleteCategory(Category $category) //automatically find object by id 
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($category);
        $entityManager->flush();
        return $this->redirectToRoute('categories');
    }

    public function getAllCategories(CategoryTreeAdminOptionList $categories,
    $editedCategory = null)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $categories->getCategoryList($categories->buildTree());
        return $this->render('admin/_all_categories.html.twig',[
            'categories' => $categories,
            'editedCategory' => $editedCategory
        ]);
    }

    private function saveCategory($category, $form, $request)
    {
        $form->handleRequest($request);
       
        if($form->isSubmitted() && $form->isValid())
        {
            $category->setName($request->request->get('category')['name']);
            
            $repository = $this->getDoctrine()->getRepository(Category::class);
            $parent = $repository->find($request->request->get('category')['parent']);
            $category->setParent($parent);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return true;
        }
        return false; 
    }

    /**
     * @Route("/delete-account", name="delete_account")
     */
    public function deleteAccount() //automatically find object by id 
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($this->getUser());
        $entityManager->remove($user);
        $entityManager->flush();

        session_destroy();
        return $this->redirectToRoute('main_page');
    }
}
