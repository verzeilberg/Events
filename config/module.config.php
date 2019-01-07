<?php

namespace Event;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

return [
    'controllers' => [
        'factories' => [
            Controller\EventController::class => Factory\EventControllerFactory::class,
            Controller\EventCategoryController::class => Factory\EventCategoryControllerFactory::class,
        ],
        'aliases' => [
            'eventbeheer' => Controller\EventController::class,
            'eventcategorybeheer' => Controller\EventCategoryController::class,
        ],
    ],
    'service_manager' => [
        'invokables' => [
            'Event\Service\eventServiceInterface' => 'Event\Service\eventService',
            'Event\Service\eventCategoryServiceInterface' => 'Event\Service\eventCategoryService'
        ],
    ],
    // The following section is new and should be added to your file
    'router' => [
        'routes' => [
            'event' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/event[/:action][/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => 'eventbeheer',
                        'action' => 'index',
                    ],
                ],
            ],
            'eventCategory' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/event/category[/:action][/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => 'eventcategorybeheer',
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'event' => __DIR__ . '/../view',
        ],
    ],
    // The 'access_filter' key is used by the User module to restrict or permit
    // access to certain controller actions for unauthorized visitors.
    'access_filter' => [
        'controllers' => [
            'eventbeheer' => [
                // to anyone.
                ['actions' => '*', 'allow' => '+event.manage'],
            ],
            'eventcategorybeheer' => [
                // to anyone.
                ['actions' => '*', 'allow' => '+event.manage'],
            ],
        ]
    ],
    'doctrine' => [
        'driver' => [
            __NAMESPACE__ . '_driver' => [
                'class' => AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [__DIR__ . '/../src/Entity']
            ],
            'orm_default' => [
                'drivers' => [
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                ]
            ]
        ]
    ],
    'asset_manager' => [
        'resolver_configs' => [
            'paths' => [
                __DIR__ . '/../public',
            ],
        ],
    ],
];
