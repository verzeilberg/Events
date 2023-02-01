<?php
namespace Event\Form;

use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Doctrine\Persistence\ObjectManager;
use Laminas\Form\Form;

class UpdateEventForm extends Form
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('update-blog-form');

        // The form will hydrate an object of type "Blog"
        $this->setHydrator(new DoctrineHydrator($objectManager));

        // Add the Blog fieldset, and set it as the base fieldset
        $blogFieldset = new EventFieldset($objectManager);
        $blogFieldset->setUseAsBaseFieldset(true);
        $this->add($blogFieldset);

        // … add CSRF and submit elements …

        // Optionally set your validation group here
    }
}