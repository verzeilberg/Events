<?php

namespace Event\Form;

use Event\Entity\EventCategory;
use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Doctrine\Persistence\ObjectManager;
use DoctrineModule\Form\Element\ObjectMultiCheckbox;
use DoctrineModule\Form\Element\ObjectSelect;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;

class EventCategoryFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('event-category-fieldset');

        $this->setHydrator(new DoctrineHydrator($objectManager))
            ->setObject(new EventCategory());

        $this->add([
            'type'  => Text::class,
            'name' => 'name',
            'attributes' => [
                'id' => 'name',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Name',
            ],
        ]);

        $this->add([
            'type'  => Textarea::class,
            'name' => 'description',
            'attributes' => [
                'id' => 'description',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => 'Description',
            ],
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'name' => [
                'required' => false,
            ]
        ];
    }
}
