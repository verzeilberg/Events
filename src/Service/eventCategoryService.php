<?php

namespace Event\Service;

use Laminas\ServiceManager\ServiceLocatorInterface;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;
use Event\Entity\EventCategory;

class eventCategoryService implements eventCategoryServiceInterface {

    /**
     * Constructor.
     */
    public function __construct($entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     *
     * Get array of events 
     *
     * @return      array
     *
     */
    public function getEventCategories() {
        $eventCategories = $this->entityManager->getRepository(EventCategory::class)
                ->findBy(['deleted' => 0], ['name' => 'DESC']);

        return $eventCategories;
    }

    public function createEventsArrayForMaps($events) {
        $locations = [];
        if ($events != null) {
            foreach ($events AS $event) {

                if (!empty($event->getLatitude()) && !empty($event->getLongitude())) {
                    $location = [];
                    $location[] = $event->getLabelText();
                    $location[] = $event->getLatitude();
                    $location[] = $event->getLongitude();
                    $location[] = '/img/icons/google-maps/bullseye.svg';
                    $locations[] = $location;
                }
            }
        }

        return $locations;
    }

    /**
     *
     * Get event object
     *
     * @return      object
     *
     */
    public function getUpcommingEvent() {

        $currentDate = new \DateTime();
        $qb = $this->entityManager->getRepository('Event\Entity\Event')->createQueryBuilder('e');
        $qb->select('e')
                ->where('e.eventStartDate >= :identifier')
                ->andWhere('e.deleted = 0')
                ->orderBy('e.eventStartDate', 'ASC')
                ->setParameter('identifier', $currentDate)
                ->setMaxResults(1);

        $query = $qb->getQuery();
        $result = $query->getResult();

        return $result[0];
    }

    /**
     *
     * Get event object
     *
     * @return      object
     *
     */
    public function getEventsByYear($year = null) {
        if ($year != null) {
            $beginYear = new \DateTime("$year-1-1 00:00:00");
            $endYear   = new \DateTime("$year-12-31 23:59:59");
            $qb = $this->entityManager->getRepository('Event\Entity\Event')->createQueryBuilder('e');
            $qb->select('e')
                    ->where('e.eventStartDate >= :beginOfYear')
                    ->andWhere('e.eventEndDate <= :endOfYear')
                    ->andWhere('e.deleted = 0')
                    ->orderBy('e.eventStartDate', 'DESC')
                    ->setParameter('beginOfYear', $beginYear)
                    ->setParameter('endOfYear', $endYear);

            $query = $qb->getQuery();
            $result = $query->getResult();
            
            return $result;
        } else {
            return null;
        }
    }

    /**
     *
     * Get array of archived event ctegories
     *
     * @return      array
     *
     */
    public function getArchivedEventCategories() {
        $eventCategories = $this->entityManager->getRepository(EventCategory::class)
                ->findBy(['deleted' => 1], ['id' => 'ASC']);

        return $eventCategories;
    }

    /**
     *
     * Get object of an event category by id
     *
     * @param       id $id of the event category
     * @return      object
     *
     */
    public function getEventCategoryById($id) {
        $eventCategory = $this->entityManager->getRepository(EventCategory::class)
                ->findOneBy(['id' => $id], []);

        return $eventCategory;
    }

    /**
     *
     * Create a new Event object
     *
     * @return      object
     *
     */
    public function createEventCategory() {
        return new EventCategory();
    }

    /**
     *
     * Delete a Event category object from database
     * @param       event $eventCategory object
     * @return      object
     *
     */
    public function deleteEventCategory($eventCategory) {
        $this->entityManager->remove($eventCategory);
        $this->entityManager->flush();
    }

    /**
     *
     * Set data to new event
     *
     * @param       event $event object
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function setNewEventCategory($eventCategory, $currentUser) {
        $eventCategory->setDateCreated(new \DateTime());
        $eventCategory->setCreatedBy($currentUser);

        $this->storeEventCategory($eventCategory);
    }

    /**
     *
     * Set data to existing event category
     *
     * @param       event $eventCategory object
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function setExistingEventCategory($eventCategory, $currentUser) {
        $eventCategory->setDateChanged(new \DateTime());
        $eventCategory->setChangedBy($currentUser);
        $this->storeEventCategory($eventCategory);
    }

    /**
     *
     * Archive event category
     *
     * @param       event $eventCategory object
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function archiveEventCategory($eventCategory, $currentUser) {
        $eventCategory->setDateDeleted(new \DateTime());
        $eventCategory->setDeleted(1);
        $eventCategory->setDeletedBy($currentUser);

        $this->storeEventCategory($eventCategory);
    }

    /**
     *
     * UnArchive event category
     *
     * @param       event $eventCategory object
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function unArchiveEventCategory($eventCategory, $currentUser) {
        $eventCategory->setDeletedBy(NULL);
        $eventCategory->setChangedBy($currentUser);
        $eventCategory->setDeleted(0);
        $eventCategory->setDateDeleted(NULL);
        $eventCategory->setDateChanged(new \DateTime());

        $this->storeEventCategory($eventCategory);
    }

    /**
     *
     * Save event to database
     *
     * @param       event object
     * @return      void
     *
     */
    public function storeEventCategory($eventCategory) {
        $this->entityManager->persist($eventCategory);
        $this->entityManager->flush();
    }

    /**
     *
     * Create form of an object
     *
     * @param       event $event object
     * @return      form
     *
     */
    public function createEventCategoryForm($eventCategory) {
        $builder = new AnnotationBuilder($this->entityManager);
        $form = $builder->createForm($eventCategory);
        $form->setHydrator(new DoctrineHydrator($this->entityManager, 'Event\Entity\EventCategory'));
        $form->bind($eventCategory);

        return $form;
    }

}
