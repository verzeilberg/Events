<?php

namespace Event\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;
use Zend\Form\Form;
use Event\Entity\Event;
use Zend\Session\Container;
use UploadImages\Entity\Image;
use UploadImages\Entity\ImageType;

/*
 * Entities
 */

class EventCategoryController extends AbstractActionController {

    protected $vhm;
    protected $em;
    private $viewhelpermanager;
    private $eventCategoryService;
    private $uploadfilesService;

    public function __construct($vhm, $em, $viewhelpermanager, $eventCategoryService, $uploadfilesService) {
        $this->vhm = $vhm;
        $this->em = $em;
        $this->viewhelpermanager = $viewhelpermanager;
        $this->eventCategoryService = $eventCategoryService;
        $this->uploadfilesService = $uploadfilesService;
    }

    public function indexAction() {
        $this->layout('layout/beheer');
        $eventCategories = $this->eventCategoryService->getEventCategories();
        return new ViewModel(
                array(
            'eventCategories' => $eventCategories
                )
        );
    }

    public function addAction() {
        $this->layout('layout/beheer');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/upload-files.js');

        $eventCategory = $this->eventCategoryService->createEventCategory();
        $form = $this->eventCategoryService->createEventCategoryForm($eventCategory);
        $form = $this->uploadfilesService->addFileInputToForm($form);
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {

                if ($this->getRequest()->getFiles('fileUpload') != null) {
                    $data = $this->uploadfilesService->uploadFile($this->getRequest()->getFiles('fileUpload'), null, 'default');

                    if (is_array($data)) {

                        $file = $this->uploadfilesService->createFile();
                        $description = $this->getRequest()->getPost('fileDescription');
                        $this->uploadfilesService->setNewFile($file, $data, $description, $this->currentUser());
                        $eventCategory->setFile($file);
                    } else {
                        $this->flashMessenger()->addErrorMessage('Bestand niet opgeslagen: ' . $data);
                    }
                }

                //Save Event
                $this->eventCategoryService->setNewEventCategory($eventCategory, $this->currentUser());
                $this->flashMessenger()->addSuccessMessage('Event categorie opgeslagen');
                return $this->redirect()->toRoute('beheer/eventcategories');
            }
        }

        return new ViewModel([
            'form' => $form,
            'eventCategory' => $eventCategory
        ]);
    }

    public function editAction() {
        $this->layout('layout/beheer');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/upload-files.js');

        $id = (int) $this->params()->fromRoute('id', 0);
        if (empty($id)) {
            return $this->redirect()->toRoute('beheer/eventcategories');
        }
        $eventCategory = $this->eventCategoryService->getEventCategoryById($id);
        if (empty($eventCategory)) {
            return $this->redirect()->toRoute('beheer/eventcategories');
        }
        $form = $this->eventCategoryService->createEventCategoryForm($eventCategory);
        $form = $this->uploadfilesService->addFileInputToForm($form);
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {

                if ($this->getRequest()->getFiles('fileUpload') != null) {
                    $data = $this->uploadfilesService->uploadFile($this->getRequest()->getFiles('fileUpload'));
                    if (is_array($data)) {
                        $file = $this->uploadfilesService->createFile();
                        $description = $this->getRequest()->getPost('fileDescription');
                        $this->uploadfilesService->setNewFile($file, $data, $description, $this->currentUser());
                        $eventCategory->setFile($file);
                    } else {
                        $this->flashMessenger()->addErrorMessage('Bestand niet opgeslagen: ' . $data);
                    }
                }

                //Save Event
                $this->eventCategoryService->setExistingEventCategory($eventCategory, $this->currentUser());
                $this->flashMessenger()->addSuccessMessage('Event categorie gewijzigd');
                return $this->redirect()->toRoute('beheer/eventcategories');
            }
        }

        return new ViewModel([
            'form' => $form,
            'eventCategory' => $eventCategory
        ]);
    }

        /**
     * 
     * Action to show all deleted blogs
     */
    public function archiveAction() {
        $this->layout('layout/beheer');
        $eventCategories = $this->eventCategoryService->getArchivedEventCategories();

        return new ViewModel([
            'eventCategories' => $eventCategories
        ]);
    }
    
    public function archiefAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (empty($id)) {
            return $this->redirect()->toRoute('beheer/eventcategories');
        }
        $eventCategory = $this->eventCategoryService->getEventCategoryById($id);
        if (empty($eventCategory)) {
            return $this->redirect()->toRoute('beheer/eventcategories');
        }
        //Set changed date
        $this->eventCategoryService->archiveEventCategory($eventCategory);
        $this->flashMessenger()->addSuccessMessage('Event categorie gearchiveerd');
        return $this->redirect()->toRoute('beheer/eventcategories');
    }

    public function unArchiefAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (empty($id)) {
            return $this->redirect()->toRoute('beheer/eventcategories');
        }
        $eventCategory = $this->eventCategoryService->getEventCategoryById($id);
        if (empty($eventCategory)) {
            return $this->redirect()->toRoute('beheer/eventcategories');
        }
        //Save Event
        $this->eventCategoryService->unArchiveEventCategory($eventCategory, $this->currentUser());
        $this->flashMessenger()->addSuccessMessage('Event categorrie terug gezet');
        return $this->redirect()->toRoute('beheer/eventcategories');
    }

    /**
     * 
     * Action to delete the event from the database and linked images
     */
    public function deleteAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (empty($id)) {
            return $this->redirect()->toRoute('beheer/eventcategories');
        }
        $eventCategory = $this->eventCategoryService->getEventCategoryById($id);
        if (empty($eventCategory)) {
            return $this->redirect()->toRoute('beheer/eventcategories');
        }
        //Delete linked file
        $file = $eventCategory->getFile();
        if (count($file) > 0) {
            $this->uploadfilesService->deleteFile($file);
        }
        // Remove event category
        $this->eventCategoryService->deleteEventCategory($eventCategory);
        $this->flashMessenger()->addSuccessMessage('Event categorie verwijderd');
        return $this->redirect()->toRoute('beheer/eventcategories');
    }

}
