<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\Subscription;
use App\Controller\Traits\SaveSubscription;
class SubscriptionController extends AbstractController
{
    use SaveSubscription;
     /**
     * @Route("/pricing", name="pricing")
     */
    public function pricing(): Response
    {
        return $this->render('front/pricing.html.twig',[
            'name' => Subscription::getPlanDataNames(),
            'price' => Subscription::getPlanDataPrices()
        ]);
    }

     /**
     * @Route("/payment/{paypal}", name="payment", defaults = {"paypal": false})
     */
    public function payment($paypal, SessionInterface $session): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        if($paypal)
        {
            $this->saveSubscription($session->get('planName'), $this->getUser());

            return $this->redirectToRoute('admin_main_page');
        }
        return $this->render('front/payment.html.twig');
    }
}
