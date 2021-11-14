<?php

namespace App\Listeners;

use App\Entity\Video;
use App\Entity\User;
use Doctrine\ORM\Event\LifecycleEventArgs as EventLifecycleEventArgs;

class NewVideoListener
{

    public function postPersist(EventLifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        // only act on some "Product" entity
        if (!$entity instanceof Video) {
            return;
        }


        $entityManager = $args->getObjectManager();
        // ... do something with the Product

        $users = $entityManager->getRepository(User::class)->findAll();

       foreach($users as $user)
        {
             exit( $user->getName().' '.$entity->getTitle());
        }

    }
}

