<?php

namespace Event\Controller;

use Blog\Entity\Blog;
use Blog\Entity\Cat;
use Blog\Entity\Comment;
use Event\Entity\EventCategory;
use Event\Form\CreateEventForm;
use Event\Form\UpdateEventForm;
use Laminas\Form\Annotation\AnnotationBuilder;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Doctrine\ORM\EntityManager;
use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Laminas\Form\Form;
use Event\Entity\Event;
use Laminas\Session\Container;
use Symfony\Component\VarDumper\VarDumper;
use UploadImages\Entity\Image;
use UploadImages\Entity\ImageType;

class EventController extends AbstractActionController
{

    protected $vhm;
    protected $em;
    private $viewhelpermanager;
    private $cropImageService;
    private $imageService;
    private $eventService;

    public function __construct($vhm, $em, $viewhelpermanager, $cropImageService, $imageService, $eventService)
    {
        $this->vhm = $vhm;
        $this->em = $em;
        $this->viewhelpermanager = $viewhelpermanager;
        $this->cropImageService = $cropImageService;
        $this->imageService = $imageService;
        $this->eventService = $eventService;
    }

    public function indexAction()
    {
        $this->layout('layout/beheer');
        $page = $this->params()->fromQuery('page', 1);
        $query = $this->eventService->getEvents();

        $searchString = '';
        if ($this->getRequest()->isPost()) {
            $searchString = $this->getRequest()->getPost('search');
            $query = $this->eventService->searchEvents($searchString);
        }

        $events = $this->eventService->getItemsForPagination($query, $page, 10);

        return new ViewModel(
            [
                'events' => $events,
                'searchString' => $searchString
            ]
        );
    }

    /**
     *
     * Action to show all deleted blogs
     */
    public function archiveAction()
    {
        $this->layout('layout/beheer');
        $page = $this->params()->fromQuery('page', 1);
        $query = $this->eventService->getArchivedEvents();
        $events = $this->eventService->getItemsForPagination($query, $page, 1);

        return new ViewModel([
            'events' => $events
        ]);
    }

    public function addAction()
    {
        $this->layout('layout/beheer');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/events.js');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/timeshift/timeshift-1.0.js');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/timeshift/dateshift-1.0.js');
        $this->viewhelpermanager->get('headLink')->appendStylesheet('/css/timeshift/timeshift-1.0.css');
        $this->viewhelpermanager->get('headLink')->appendStylesheet('/css/timeshift/dateshift-1.0.css');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/uploadImages.js');
        $this->viewhelpermanager->get('headLink')->appendStylesheet('/css/events.css');

        $container = new Container('cropImages');
        $container->getManager()->getStorage()->clear('cropImages');

        // Create the form and inject the EntityManager
        $form = new CreateEventForm($this->em);
        // Create a new, empty entity and bind it to the form
        $event = $this->eventService->createEvent();


        $form->bind($event);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {

                //Create image array and set it
                $imageFile = $this->getRequest()->getFiles('upload-image')['image'][0];

                //Upload image
                if ($imageFile['error'] === 0) {
                    //Upload original file
                    $imageFiles = $this->cropImageService->uploadImage($imageFile, 'event', 'original', $image, 1);
                    if (is_array($imageFiles)) {
                        $folderOriginal = $imageFiles['imageType']->getFolder();
                        $fileName = $imageFiles['imageType']->getFileName();
                        $image = $imageFiles['image'];
                        //Upload thumb 150x100
                        $imageFiles = $this->cropImageService->resizeAndCropImage('public/' . $folderOriginal . $fileName, 'public/img/userFiles/event/thumb/', 150, 150, '150x150', $image);
                        //Resize image
                        $imageFiles = $this->cropImageService->ResizeImage('public/' . $folderOriginal . $fileName, 'public/img/userFiles/event/resized/', 100, null, 'resized', $image);
                        //Create 400x300 crop
                        $imageFiles = $this->cropImageService->createCropArray('400x200', $folderOriginal, $fileName, 'public/img/userFiles/event/400x200/', 400, 200, $image);
                        $image = $imageFiles['image'];
                        $cropImages = $imageFiles['cropImages'];
                        //Create 800x600 crop
                        $imageFiles = $this->cropImageService->createCropArray('800x600', $folderOriginal, $fileName, 'public/img/userFiles/event/800x600/', 800, 600, $image, $cropImages);
                        $image = $imageFiles['image'];
                        $cropImages = $imageFiles['cropImages'];
                        //Create return URL
                        $returnURL = $this->cropImageService->createReturnURL('beheer/event', 'index');

                        //Create session container for crop
                        $this->cropImageService->createContainerImages($cropImages, $returnURL);

                        //Save blog image
                        $this->imageService->saveImage($image);
                        //Add image to club
                        $event->setEventImage($image);
                    } else {
                        $this->flashMessenger()->addErrorMessage($imageFiles);
                    }
                } else {
                    $this->flashMessenger()->addErrorMessage('Image not uploaded');
                }
                //End upload image
                //Save Event
                $this->eventService->setNewEvent($event, $this->currentUser());
                $this->flashMessenger()->addSuccessMessage('Event opgeslagen');

                if ($imageFile['error'] === 0 && is_array($imageFiles)) {
                    return $this->redirect()->toRoute('beheer/images', array('action' => 'crop'));
                } else {
                    return $this->redirect()->toRoute('beheer/event');
                }
            } else {
                foreach($form->getMessages()['event'] as $message)  {
                    $this->flashMessenger()->addErrorMessage('Image not uploaded');
                }
            }
        }

        $returnURL = $this->cropImageService->createReturnURL('beheer/event', 'index');

        return new ViewModel([
            'form' => $form,
            'returnURL' => $returnURL
        ]);
    }

    public function editAction()
    {
        //First include layout, js and css files
        $this->layout('layout/beheer');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/events.js');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/timeshift/timeshift-1.0.js');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/timeshift/dateshift-1.0.js');
        $this->viewhelpermanager->get('headLink')->appendStylesheet('/css/timeshift/timeshift-1.0.css');
        $this->viewhelpermanager->get('headLink')->appendStylesheet('/css/timeshift/dateshift-1.0.css');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/uploadImages.js');
        $this->viewhelpermanager->get('headLink')->appendStylesheet('/css/events.css');

        //Create new container for crop images
        $container = new Container('cropImages');
        $container->getManager()->getStorage()->clear('cropImages');


        $id = (int)$this->params()->fromRoute('id', 0);
        if (empty($id)) {
            return $this->redirect()->toRoute('beheer/event');
        }
        $event = $this->eventService->getEventById($id);
        if (empty($event)) {
            return $this->redirect()->toRoute('beheer/event');
        }

        // Create the form and inject the EntityManager
        $form = new UpdateEventForm($this->em);
        // Create a new, empty entity and bind it to the form
        $form->bind($event);
        $Image = $this->imageService->createImage();
        $formEventImage = $this->imageService->createImageForm($Image);

        if ($this->getRequest()->isPost()) {

            $form->setData($this->getRequest()->getPost());
            $formEventImage->setData($this->getRequest()->getPost());
            if ($form->isValid() && $formEventImage->isValid()) {
                //Create image array and set it
                $imageFile = $this->getRequest()->getFiles('upload-image')['image'][0];

                //Upload image
                if ($imageFile['error'] === 0) {
                    //Upload original file
                    $imageFiles = $this->cropImageService->uploadImage($imageFile, 'event', 'original', $image, 1);
                    if (is_array($imageFiles)) {
                        $folderOriginal = $imageFiles['imageType']->getFolder();
                        $fileName = $imageFiles['imageType']->getFileName();
                        $image = $imageFiles['image'];
                        //Upload thumb 150x100
                        $imageFiles = $this->cropImageService->resizeAndCropImage('public/' . $folderOriginal . $fileName, 'public/img/userFiles/event/thumb/', 150, 150, '150x150', $image);
                        //Resize image
                        $imageFiles = $this->cropImageService->ResizeImage('public/' . $folderOriginal . $fileName, 'public/img/userFiles/event/resized/', 100, null, 'resized', $image);
                        //Create 400x300 crop
                        $imageFiles = $this->cropImageService->createCropArray('400x200', $folderOriginal, $fileName, 'public/img/userFiles/event/400x200/', 400, 200, $image);
                        $image = $imageFiles['image'];
                        $cropImages = $imageFiles['cropImages'];
                        //Create 800x600 crop
                        $imageFiles = $this->cropImageService->createCropArray('800x600', $folderOriginal, $fileName, 'public/img/userFiles/event/800x600/', 800, 600, $image, $cropImages);
                        $image = $imageFiles['image'];
                        $cropImages = $imageFiles['cropImages'];
                        //Create return URL
                        $returnURL = $this->cropImageService->createReturnURL('beheer/event', 'index');
                        //Create session container for crop
                        $this->cropImageService->createContainerImages($cropImages, $returnURL);

                        //Save blog image
                        $this->imageService->saveImage($image);
                        //Add image to club
                        $event->setEventImage($image);
                    } else {
                        $this->flashMessenger()->addErrorMessage($imageFiles);
                    }
                } else {
                    $this->flashMessenger()->addErrorMessage('Image not uploaded');
                }
                //End upload image
                //Save Event
                $this->eventService->setExistingEvent($event, $this->currentUser());
                $this->flashMessenger()->addSuccessMessage('Event opgeslagen');

                if ($imageFile['error'] === 0 && is_array($imageFiles)) {
                    return $this->redirect()->toRoute('beheer/images', array('action' => 'crop'));
                } else {
                    return $this->redirect()->toRoute('beheer/event');
                }
            }
        }

        $returnURL = $this->cropImageService->createReturnURL('beheer/event', 'edit', $id);

        return new ViewModel([
            'form' => $form,
            'formEventImage' => $formEventImage,
            'image' => $event->getEventImage(),
            'latitude' => $event->getLatitude(),
            'longitude' => $event->getLongitude(),
            'returnURL' => $returnURL
        ]);
    }

    public function archiefAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        if (empty($id)) {
            return $this->redirect()->toRoute('beheer/blog');
        }
        $event = $this->eventService->getEventById($id);
        if (empty($event)) {
            return $this->redirect()->toRoute('beheer/event');
        }
        //Set changed date
        $this->eventService->archiveEvent($event, $this->currentUser());
        $this->flashMessenger()->addSuccessMessage('Event gearchiveerd');
        return $this->redirect()->toRoute('beheer/event');
    }

    public function unArchiefAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        if (empty($id)) {
            return $this->redirect()->toRoute('beheer/event');
        }
        $event = $this->eventService->getEventById($id);
        if (empty($event)) {
            return $this->redirect()->toRoute('beheer/event');
        }
        //Save Event
        $this->eventService->unArchiveEvent($event, $this->currentUser());
        $this->flashMessenger()->addSuccessMessage('Event terug gezet');
        return $this->redirect()->toRoute('beheer/event');
    }

    /**
     *
     * Action to delete the event from the database and linked images
     */
    public function deleteAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        if (empty($id)) {
            return $this->redirect()->toRoute('beheer/event');
        }
        $event = $this->eventService->getEventById($id);
        if (empty($event)) {
            return $this->redirect()->toRoute('beheer/event');
        }

        //Delete linked images
        $image = $event->getEventImage();
        if (is_object($image)) {
            $this->imageService->deleteImage($image);
        }
        // Remove blog
        $this->eventService->deleteEvent($event);
        $this->flashMessenger()->addSuccessMessage('Event verwijderd');
        $this->redirect()->toRoute('beheer/event', array('action' => 'archive'));
    }

}
