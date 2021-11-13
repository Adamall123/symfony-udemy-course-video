<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Video;
use App\Utils\CategoryTreeFrontPage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\VideoRepository;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Utils\VideoForNoValidSubscription;


use App\Entity\Subscription;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FrontController extends AbstractController
{
    /**
     * @Route("/", name="main_page")
     */
    public function index(): Response
    {
        return $this->render('front/index.html.twig');
    }
    /**
     * @Route("/video-list/category/{categoryname},{id}/{page}", defaults={"page": "1"} , name="video_list")
     */
    public function videoList($id, $page, CategoryTreeFrontPage $categories, 
    Request $request, VideoForNoValidSubscription $video_no_members): Response
    {
        $categories->getCategoryListAndParent($id);
        $ids = $categories->getChildIds($id);
        array_push($ids, $id);
        $videos = $this->getDoctrine()->getRepository(Video::class)->findByChildIds($ids ,$page, $request->get('sortby'));
        // dump($categories->getCategoryListAndParent($id));
        // exit;
        return $this->render('front/video_list.html.twig',[
            //'subcategories' => $categories->getCategoryList($subcategories)
            'subcategories' => $categories,
            'videos' => $videos,
            'video_no_members' => $video_no_members->check()
        ]);
    }

    /**
     * @Route("/video-details/{video}", name="video_details")
     */
    public function videoDetails(VideoRepository $repo, $video,VideoForNoValidSubscription $video_no_members): Response
    {
        return $this->render('front/video_details.html.twig',[
            'video' => $repo->videoDetails($video),
            'video_no_members' => $video_no_members->check()
        ]);
    }

    /**
     * @Route("/search-results", methods={"POST"}, name="search_results")
     */
    public function searchResults(): Response
    {
        return $this->render('front/search_results.html.twig');
    }

     

      /**
     * @Route("/register/{plan}", name="register", defaults={"plan": null})
     */
    public function register(Request $request, UserPasswordEncoderInterface $password_encoder, SessionInterface $session, $plan): Response
    {
        if($request->isMethod('GET'))
        {
            $session->set('planName',$plan);
            $session->set('planPrice', Subscription::getPlanDataPriceByName($plan));
            
        }
        $user = new User; 
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $entityManager = $this->getDoctrine()->getManager();

            $user->setName($request->request->get('user')['name']);
            $user->setLastName($request->request->get('user')['last_name']);
            $user->setEmail($request->request->get('user')['email']);
            $password = $password_encoder->encodePassword($user,$request->request->get('user')['password']['first']);
            $user->setPassword($password);
            $user->setRoles(['ROLE_USER']);

            $entityManager->persist($user);
            $entityManager->flush();
            $this->loginUserAutomatically($user, $password);

            return $this->redirectToRoute('admin_main_page');
        }
        if($this->isGranted('IS_AUTHENTICATED_REMEMBERED') && $plan == 
            Subscription::getPlanDataNameByIndex(0)) //free plan
            {
                //to do save subscription
                return $this->redirectToRoute('admin_main_page');
            }
            elseif($this->isGranted('IS_AUTHENTICATED_REMEMBERED'))
            {
                return $this->redirectToRoute('payment');
            }
        return $this->render('front/register.html.twig',[
            'form' => $form->createView()
        ]);
    }

     /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $helper): Response
    {
        return $this->render('front/login.html.twig',[
            'error' => $helper->getLastAuthenticationError()
        ]);
    }

    private function loginUserAutomatically($user, $password)
    {
        $token = new UsernamePasswordToken(
            $user,
            $password,
            'main',
            $user->getRoles()
        );
        $this->get('security.token_storage')->setToken($token);
        $this->get('session')->set('_security_main', serialize($token));
    }
    /**
     * @Route("/logout", name="logout")
     */
    public function logout(): void
    {
       throw new \Exception('This should nevere be reeached!');
    }

    

    /**
     * @Route("/new-comment/{video}", methods={"POST"}, name="new_comment")
    */
    public function newComment(Video $video, Request $request )
     {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        
        if ( !empty( trim($request->request->get('comment')) ) ) 
        {   

            // $video = $this->getDoctrine()->getRepository(Video::class)->find($video_id);
        
            $comment = new Comment();
            $comment->setContent($request->request->get('comment'));
            $comment->setUser($this->getUser());
            $comment->setVideo($video);

            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();
        }
        
        return $this->redirectToRoute('video_details',['video'=>$video->getId()]);
     }
    public function mainCategories()
    {
        $categories = $this->getDoctrine()->getRepository(Category::class)->findBy(['parent' => null], ['name'=>'ASC']);
        return $this->render('front/_main_categories.html.twig',[
            'categories' => $categories
        ]);
    }
     /**
     * @Route("/video-list/{video}/like", name="like_video", methods={"POST"})
     * @Route("/video-list/{video}/dislike", name="dislike_video", methods={"POST"})
     * @Route("/video-list/{video}/unlike", name="undo_like_video", methods={"POST"})
     * @Route("/video-list/{video}/undodislike", name="undo_dislike_video", methods={"POST"})
     */
    public function toggleLikesAjax(Video $video, Request $request)
    {
        
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        switch($request->get('_route'))
        {
            case 'like_video':
            $result = $this->likeVideo($video);
            break;
            
            case 'dislike_video':
            $result = $this->dislikeVideo($video);
            break;

            case 'undo_like_video':
            $result = $this->undoLikeVideo($video);
            break;

            case 'undo_dislike_video':
            $result = $this->undoDislikeVideo($video);
            break;
        }

        return $this->json(['action' => $result,'id'=>$video->getId()]);
    }

    private function likeVideo($video)
    {  
        return 'liked';
    }
    private function dislikeVideo($video)
    {
        return 'disliked';
    }
    private function undoLikeVideo($video)
    {  
        return 'undo liked';
    }
    private function undoDislikeVideo($video)
    {   
        return 'undo disliked';
    }
}
