<?php

namespace Event\Form;

use Event\Form\EventFieldset;
use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Doctrine\Persistence\ObjectManager;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Submit;
use Laminas\Form\Form;
use UploadImages\Form\UploadImageFieldset;

class CreateEventForm extends Form
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('create-event-form');

        // The form will hydrate an object of type "Blog"
        $this->setHydrator(new DoctrineHydrator($objectManager));

        // Add the Event fieldset, and set it as the base fieldset
        $eventFieldset = new EventFieldset($objectManager);
        $eventFieldset->setUseAsBaseFieldset(true);
        $this->add($eventFieldset);
        // Add the Upload image fieldset, and set it as the base fieldset
        $uploadImageFieldset = new UploadImageFieldset($objectManager);
        $uploadImageFieldset->setUseAsBaseFieldset(false);
        $this->add($uploadImageFieldset);

        // Add the Submit button
        $this->add([
            'type'  => Submit::class,
            'name' => 'submit',
            'attributes' => [
                'value' => 'Toevoegen',
                'id' => 'submit',
                'class' => 'btn btn-primary',
            ],
        ]);

        // Add the CSRF field
        $this->add([
            'type' => Csrf::class,
            'name' => 'csrf',
            'options' => [
                'csrf_options' => [
                    'timeout' => 600
                ]
            ],
        ]);

    }
}