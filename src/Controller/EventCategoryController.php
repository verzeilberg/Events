<?php

namespace Event\Controller;

use Event\Form\CreateEventCategoryForm;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;
use Laminas\Form\Form;
use Event\Entity\Event;
use Laminas\Session\Container;
use Symfony\Component\VarDumper\VarDumper;
use UploadFiles\Service\uploadfilesService;
use UploadImages\Entity\Image;
use UploadImages\Entity\ImageType;

/*
 * Entities
 */

class EventCategoryController extends AbstractActionController
{

    protected $vhm;
    protected $em;
    private $viewhelpermanager;
    private $eventCategoryService;
    private uploadfilesService $uploadfilesService;

    public function __construct($vhm, $em, $viewhelpermanager, $eventCategoryService, $uploadfilesService)
    {
        $this->vhm = $vhm;
        $this->em = $em;
        $this->viewhelpermanager = $viewhelpermanager;
        $this->eventCategoryService = $eventCategoryService;
        $this->uploadfilesService = $uploadfilesService;
    }

    public function indexAction()
    {
        $this->layout('layout/beheer');
        $page = $this->params()->fromQuery('page', 1);
        $query = $this->eventCategoryService->getEventCategories();

        $searchString = '';
        if ($this->getRequest()->isPost()) {
            $searchString = $this->getRequest()->getPost('search');
            $query = $this->eventCategoryService->searchEventCategorie($searchString);
        }

        $eventCategories = $this->eventCategoryService->getItemsForPagination($query, $page, 10);

        return new ViewModel(
            [
                'eventCategories' => $eventCategories,
                'searchString' => $searchString
            ]
        );
    }

    public function addAction()
    {
        $this->layout('layout/beheer');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/upload-files.js');

        // Create the form and inject the EntityManager
        $form = new CreateEventCategoryForm($this->em);
        // Create a new, empty entity and bind it to the form
        $eventCategory = $this->eventCategoryService->createEventCategory();
        $form->bind($eventCategory);

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

    public function editAction()
    {
        $this->layout('layout/beheer');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/upload-files.js');

        $id = (int)$this->params()->fromRoute('id', 0);
        if (empty($id)) {
            return $this->redirect()->toRoute('beheer/eventcategories');
        }
        $eventCategory = $this->eventCategoryService->getEventCategoryById($id);
        if (empty($eventCategory)) {
            return $this->redirect()->toRoute('beheer/eventcategories');
        }


        // Create the form and inject the EntityManager
        $form = new CreateEventCategoryForm($this->em);
        // Create a new, empty entity and bind it to the form
        $form->bind($eventCategory);
        //$form = $this->uploadfilesService->addFileInputToForm($form);
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
    public function archiveAction()
    {
        $this->layout('layout/beheer');
        $page = $this->params()->fromQuery('page', 1);
        $query = $this->eventCategoryService->getArchivedEventCategories();

        $searchString = '';
        if ($this->getRequest()->isPost()) {
            $searchString = $this->getRequest()->getPost('search');
            $query = $this->eventCategoryService->searchEventCategorie($searchString, 1);
        }

        $eventCategories = $this->eventCategoryService->getItemsForPagination($query, $page, 10);

        return new ViewModel([
            'eventCategories' => $eventCategories,
            'searchString' => $searchString
        ]);
    }

    public function archiefAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        if (empty($id)) {
            return $this->redirect()->toRoute('beheer/eventcategories');
        }
        $eventCategory = $this->eventCategoryService->getEventCategoryById($id);
        if (empty($eventCategory)) {
            return $this->redirect()->toRoute('beheer/eventcategories');
        }

        //Set changed date
        $this->eventCategoryService->archiveEventCategory($eventCategory, $this->currentUser());
        $this->flashMessenger()->addSuccessMessage('Event categorie gearchiveerd');
        return $this->redirect()->toRoute('beheer/eventcategories');
    }

    public function unArchiefAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
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
    public function deleteAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        if (empty($id)) {
            return $this->redirect()->toRoute('beheer/eventcategories');
        }
        $eventCategory = $this->eventCategoryService->getEventCategoryById($id);
        if (empty($eventCategory)) {
            return $this->redirect()->toRoute('beheer/eventcategories');
        }
        //Delete linked file
        $file = $eventCategory->getFile();

        if ($file) {
            $this->uploadfilesService->removeFile($file->getPath());
        }
        // Remove event category
        $this->eventCategoryService->deleteEventCategory($eventCategory);
        $this->flashMessenger()->addSuccessMessage('Event categorie verwijderd');
        return $this->redirect()->toRoute('beheer/eventcategories', ['action' => 'archive']);
    }

}
