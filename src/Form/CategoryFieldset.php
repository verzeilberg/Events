<?php

namespace Event\Form;

use Event\Entity\EventCategory;
use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Doctrine\Persistence\ObjectManager;
use DoctrineModule\Form\Element\ObjectMultiCheckbox;
use DoctrineModule\Form\Element\ObjectSelect;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;

class CategoryFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('event-category');

        $this->setHydrator(new DoctrineHydrator($objectManager))
            ->setObject(new EventCategory());

        $this->add([
            'name' => 'category',
            'required' => false,
            'type' => ObjectSelect::class,
            'options' => [
                'object_manager' => $objectManager,
                'target_class'   => EventCategory::class,
                'property'       => 'id',
                'is_method'      => false,
                'display_empty_item' => false,
                'label' => 'Categorieeen',
                'find_method'    => array(
                    'name'   => 'findBy',
                    'params' => array(
                        'criteria' => array('deleted' => 0),
                        'orderBy'  => array('name' => 'ASC'),
                    ),
                ),
                'label_generator' => function ($targetEntity) {
                    return $targetEntity->getName();
                },
            ],
            'attributes' => [
                'class' => 'form-select',
            ],
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'categories' => [
                'required' => false,
            ]
        ];
    }
}
