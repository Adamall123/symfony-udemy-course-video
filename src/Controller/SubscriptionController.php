<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\Subscription;

class SubscriptionController extends AbstractController
{
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
}
