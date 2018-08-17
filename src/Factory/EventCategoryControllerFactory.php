<?php

namespace Event\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Event\Controller\EventCategoryController;
use Event\Service\eventCategoryService;
use UploadFiles\Service\uploadfilesService;

/**
 * This is the factory for AuthController. Its purpose is to instantiate the controller
 * and inject dependencies into its constructor.
 */
class EventCategoryControllerFactory implements FactoryInterface {

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {
        $em = $container->get('doctrine.entitymanager.orm_default');
        $vhm = $container->get('ViewHelperManager');
        $config = $container->get('config');
        $viewhelpermanager = $container->get('ViewHelperManager');
        $eventCategoryService = new eventCategoryService($em);
        $uploadfilesService = new uploadfilesService($config, $em);
        return new EventCategoryController($vhm, $em, $viewhelpermanager, $eventCategoryService, $uploadfilesService);
    }

}
