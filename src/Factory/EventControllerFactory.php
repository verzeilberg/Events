<?php

namespace Event\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Event\Controller\EventController;
use Event\Service\eventService;
use UploadImages\Service\cropImageService;
use UploadImages\Service\imageService;

/**
 * This is the factory for AuthController. Its purpose is to instantiate the controller
 * and inject dependencies into its constructor.
 */
class EventControllerFactory implements FactoryInterface {

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {
        $em = $container->get('doctrine.entitymanager.orm_default');
        $vhm = $container->get('ViewHelperManager');
        $viewhelpermanager = $container->get('ViewHelperManager');
        $config = $container->get('config');
        $cropImageService = new cropImageService($em, $config);
        $imageService = new imageService($em, $config);
        $eventService = new eventService($em);
        return new EventController($vhm, $em, $viewhelpermanager, $cropImageService, $imageService, $eventService);
    }

}
