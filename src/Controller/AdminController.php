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

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use App\Form\UserType;
use App\Entity\Video;
use App\Form\VideoType;

use App\Utils\Interfaces\UploadInterface;
/**
     * @Route("/admin")
     */
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="admin_main_page")
     */
    public function index(Request $request, UserPasswordEncoderInterface $password_encoder): Response
    {
        $user = $this->getUser(); 
        $form = $this->createForm(UserType::class, $user,['user'=>$user]);
        $form->handleRequest($request);
        $is_invalid = null; 
        if($form->isSubmitted() && $form->isValid())
        {
        $entityManager = $this->getDoctrine()->getManager();
        $user->setName($request->request->get('user')['name']);
        $user->setLastName($request->request->get('user')['last_name']);
        $user->setEmail($request->request->get('user')['email']);
        $password = $password_encoder->encodePassword($user, 
        $request->request->get('user')['password']['first']);
        $user->setPassword($password);
        $entityManager->persist($user);
        $entityManager->flush();
           $this->addFlash(
               'success',
               'Your changes were saved'
           );
           return $this->redirectToRoute('admin_main_page');
        }

        elseif($request->isMethod('post'))
        {
            $is_invalid = 'is-invalid';
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
     * @Route("/su/upload-video-locally", name="upload_video_locally")
     */
    public function uploadVideoLocally(Request $request, UploadInterface $fileUploader ): Response
    {
        $video = new Video(); 
        $form = $this->createForm(VideoType::class, $video);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $entityManager = $this->getDoctrine()->getManager();
            $file = $video->getUploadedVideo();
            //$fileName = 'to do';
            $fileName = $fileUploader->upload($file);
            
            $base_path = Video::uploadFolder;
            $video->setPath($base_path.$fileName[0]);
            $video->setTitle($fileName[1]);

            $entityManager->persist($video);
            $entityManager->flush();

            return $this->redirectToRoute('videos');
        }
        return $this->render('admin/upload_video_locally.html.twig',[
            'form' => $form->createView()
        ]);
    }
    /**
     * @Route("/su/users", name="users")
     */
    public function users(): Response
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        $users = $repository->findBy([], ['name' => 'ASC']);
        return $this->render('admin/users.html.twig',[
            'users' => $users
        ]);
    }
    /**
     * @Route("/videos", name="videos")
     */
    public function videos(CategoryTreeAdminOptionList $categories): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) 
        {

            $categories->getCategoryList($categories->buildTree());
            $videos = $this->getDoctrine()->getRepository(Video::class)->findBy([],['title'=>'ASC']);
            
        }
        else
        {
            $categories = null;
            $videos = $this->getUser()->getLikedVideos();
        }
        return $this->render('admin/videos.html.twig',[
            'videos'=>$videos,
            'categories'=>$categories
        ]);
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

    /**
     * @Route("/delete-user/{user}", name="delete_user")
     */
    public function deleteUser(User $user): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($user);
        $manager->flush();
        return $this->redirectToRoute('users');
    }

    /**
     * @Route("/update-video-category/{video}", methods={"POST"}, name="update_video_category")
     */
    public function updateVideoCategory(Request $request, Video $video): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $category = $this->getDoctrine()->getRepository(Category::class)->find($request->request->get('video_category'));

        $video->setCategory($category);
        $entityManager->persist($video);
        $entityManager->flush(); 

        return $this->redirectToRoute('videos');
    }
    
}
