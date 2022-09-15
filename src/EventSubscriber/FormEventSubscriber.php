<?php

namespace App\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FormEventSubscriber implements EventSubscriberInterface
{

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents():array
    {
        return [
            FormEvents::SUBMIT=>'onSubmit'
        ];
    }

    public function onSubmit(FormEvent $event)
    {
        $form = $event->getForm();


    }


}