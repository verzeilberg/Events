<?php
namespace Event\Form;

use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Doctrine\Persistence\ObjectManager;
use Event\Entity\Event;
use Laminas\Form\Element\Collection;
use Laminas\Form\Element\Date;
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
            'type'  => Date::class,
            'name' => 'eventStartDate',
            'options' => [
                'label' => 'Date start',
                'format' => 'Y-m-d H:i',
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'type'  => Date::class,
            'name' => 'eventEndDate',
            'options' => [
                'label' => 'Date end',
                'format' => 'Y-m-d H:i',
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'type'  => Text::class,
            'name' => 'title',
            'options' => [
                'label' => 'Title',
            ],
        ]);

        $this->add([
            'type'  => Text::class,
            'name' => 'longitude',
            'options' => [
                'label' => 'Longitude',
            ],
        ]);

        $this->add([
            'type'  => Text::class,
            'name' => 'latitude;',
            'options' => [
                'label' => 'Latitude;',
            ],
        ]);

        $this->add([
            'type'  => Textarea::class,
            'name' => 'labelText',
            'options' => [
                'label' => 'LabelText',
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'type'  => Textarea::class,
            'name' => 'text',
            'options' => [
                'label' => 'Text',
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $tagFieldset = new CategoryFieldset($objectManager);
        $this->add([
            'type'    => Collection::class,
            'name'    => 'categories',
            'options' => [
                'target_element' => $tagFieldset,
            ],
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'categories' => [
                'required' => false,
            ],
        ];
    }
}