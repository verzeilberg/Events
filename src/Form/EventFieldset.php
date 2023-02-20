<?php

namespace Event\Form;

use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Doctrine\Persistence\ObjectManager;
use DoctrineModule\Form\Element\ObjectSelect;
use Event\Entity\Event;
use Event\Entity\EventCategory;
use Laminas\Form\Element\Collection;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\DateTime;
use Laminas\Form\Element\DateTimeLocal;
use Laminas\Form\Element\DateTimeSelect;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;

class EventFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('event');

        $this->setHydrator(new DoctrineHydrator($objectManager))
            ->setObject(new Event());

        $this->add([
            'type' => DateTimeSelect::class,
            'name' => 'eventStartDate',
            'options' => [
                'label' => 'Date start',
                'format' => 'Y-m-d',
            ],
            'attributes' => [
                'class' => 'form-control dateOnline',
                'readonly' => 'readonly',
            ],
        ]);

        $this->add([
            'type' => DateTimeSelect::class,
            'name' => 'eventEndDate',
            'options' => [
                'label' => 'Date end',
                'format' => 'Y-m-d',
            ],
            'attributes' => [
                'class' => 'form-control dateOffline',
                'readonly' => 'readonly',
            ],
        ]);

        $this->add([
            'type' => Text::class,
            'name' => 'title',
            'options' => [
                'label' => 'Title',
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'name' => 'category',
            'required' => false,
            'type' => ObjectSelect::class,
            'options' => [
                'object_manager' => $objectManager,
                'target_class' => EventCategory::class,
                'property' => 'id',
                'is_method' => true,
                'display_empty_item' => true,
                'label' => 'Categorieeen',
                'label_generator' => function ($targetEntity) {
                    return $targetEntity->getName();
                },
            ],
            'attributes' => [
                'class' => 'form-select',
            ],
        ]);


        $this->add([
            'type' => Text::class,
            'name' => 'longitude',
            'options' => [
                'label' => 'Longitude',
            ],
            'attributes' => [
                'class' => 'form-control',
                'id' => 'longclicked',
            ],
        ]);

        $this->add([
            'type' => Text::class,
            'name' => 'latitude',
            'options' => [
                'label' => 'Latitude',
            ],
            'attributes' => [
                'class' => 'form-control',
                'id' => 'latclicked',
            ],
        ]);

        $this->add([
            'type' => Textarea::class,
            'name' => 'labelText',
            'options' => [
                'label' => 'LabelText',
            ],
            'attributes' => [
                'class' => 'form-control',
                'id' => 'editor1',
            ],
        ]);

        $this->add([
            'type' => Textarea::class,
            'name' => 'text',
            'options' => [
                'label' => 'Text',
            ],
            'attributes' => [
                'class' => 'form-control',
                'id' => 'editor2',
            ],
        ]);

    }

    public function getInputFilterSpecification()
    {
        return [
            'title' => [
                'required' => true,
            ],
            'category' => [
                'required' => true,
            ],
        ];
    }
}