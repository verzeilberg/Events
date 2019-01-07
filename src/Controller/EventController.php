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

class EventController extends AbstractActionController {

    protected $vhm;
    protected $em;
    private $viewhelpermanager;
    private $cropImageService;
    private $imageService;
    private $eventService;

    public function __construct($vhm, $em, $viewhelpermanager, $cropImageService, $imageService, $eventService) {
        $this->vhm = $vhm;
        $this->em = $em;
        $this->viewhelpermanager = $viewhelpermanager;
        $this->cropImageService = $cropImageService;
        $this->imageService = $imageService;
        $this->eventService = $eventService;
    }

    public function indexAction() {
        $this->layout('layout/beheer');
        $events = $this->eventService->getEvents();

        $searchString = '';
        if ($this->getRequest()->isPost()) {
            $searchString = $this->getRequest()->getPost('search');
            $events = $this->eventService->searchEvents($searchString);
        }

        return new ViewModel(
                array(
            'events' => $events,
            'searchString' => $searchString
                )
        );
    }

    /**
     * 
     * Action to show all deleted blogs
     */
    public function archiveAction() {
        $this->layout('layout/beheer');
        $events = $this->eventService->getArchivedEvents();

        return new ViewModel([
            'events' => $events
        ]);
    }

    public function addAction() {
        $this->layout('layout/beheer');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/dateTimePicker/bootstrap-datetimepicker.min.js');
        $this->viewhelpermanager->get('headScript')->appendFile('//cdn.ckeditor.com/4.10.0/standard/ckeditor.js');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/events.js');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/uploadImages.js');
        $this->viewhelpermanager->get('headLink')->appendStylesheet('/css/dateTimePicker/bootstrap-datetimepicker.css');
        $this->viewhelpermanager->get('headLink')->appendStylesheet('/css/events.css');
        $container = new Container('cropImages');
        $container->getManager()->getStorage()->clear('cropImages');

        $event = $this->eventService->createEvent();
        $form = $this->eventService->createEventForm($event);

        $Image = $this->imageService->createImage();
        $builder = new AnnotationBuilder($this->em);
        $formEventImage = $builder->createForm($Image);
        $formEventImage->setHydrator(new DoctrineHydrator($this->em, 'UploadImages\Entity\Image'));
        $formEventImage->bind($Image);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            $formEventImage->setData($this->getRequest()->getPost());
            if ($form->isValid() && $formEventImage->isValid()) {

                //Create image array and set it
                $imageFile = [];
                $imageFile = $this->getRequest()->getFiles('image');
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
                        //Create 450x300 crop
                        $imageFiles = $this->cropImageService->createCropArray('400x200', $folderOriginal, $fileName, 'public/img/userFiles/event/400x200/', 400, 200, $image);
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
            }
        }

        $returnURL = $this->cropImageService->createReturnURL('beheer/event', 'index');

        return new ViewModel([
            'form' => $form,
            'formEventImage' => $formEventImage,
            'returnURL' => $returnURL
        ]);
    }

    public function editAction() {
        //First include layout, js and css files
        $this->layout('layout/beheer');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/dateTimePicker/bootstrap-datetimepicker.min.js');
        $this->viewhelpermanager->get('headScript')->appendFile('//cdn.ckeditor.com/4.10.0/standard/ckeditor.js');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/events.js');
        $this->viewhelpermanager->get('headScript')->appendFile('/js/uploadImages.js');
        $this->viewhelpermanager->get('headLink')->appendStylesheet('/css/dateTimePicker/bootstrap-datetimepicker.css');
        $this->viewhelpermanager->get('headLink')->appendStylesheet('/css/events.css');

        //Create new container for crop images
        $container = new Container('cropImages');
        $container->getManager()->getStorage()->clear('cropImages');


        $id = (int) $this->params()->fromRoute('id', 0);
        if (empty($id)) {
            return $this->redirect()->toRoute('beheer/event');
        }
        $event = $this->eventService->getEventById($id);
        if (empty($event)) {
            return $this->redirect()->toRoute('beheer/event');
        }

        $form = $this->eventService->createEventForm($event);
        $Image = $this->imageService->createImage();
        $formEventImage = $this->imageService->createImageForm($Image);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            $formEventImage->setData($this->getRequest()->getPost());
            if ($form->isValid() && $formEventImage->isValid()) {
                //Create image array and set it
                $imageFile = [];
                $imageFile = $this->getRequest()->getFiles('image');
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
                        //Create 450x300 crop
                        $imageFiles = $this->cropImageService->createCropArray('400x200', $folderOriginal, $fileName, 'public/img/userFiles/event/400x200/', 400, 200, $image);
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

    public function archiefAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (empty($id)) {
            return $this->redirect()->toRoute('beheer/blog');
        }
        $event = $this->eventService->getEventById($id);
        if (empty($event)) {
            return $this->redirect()->toRoute('beheer/event');
        }
        //Set changed date
        $this->eventService->archiveEvent($event);
        $this->flashMessenger()->addSuccessMessage('Event gearchiveerd');
        return $this->redirect()->toRoute('beheer/event');
    }

    public function unArchiefAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
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
    public function deleteAction() {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (empty($id)) {
            return $this->redirect()->toRoute('beheer/event');
        }
        $event = $this->eventService->getEventById($id);
        if (empty($event)) {
            return $this->redirect()->toRoute('beheer/event');
        }

        //Delete linked images
        $image = $event->getEventImage();
        if (count($image) > 0) {
            $this->imageService->deleteImage($image);
        }
        // Remove blog
        $this->eventService->deleteEvent($event);
        $this->flashMessenger()->addSuccessMessage('Event verwijderd');
        $this->redirect()->toRoute('beheer/event', array('action' => 'archive'));
    }

}
