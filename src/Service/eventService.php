<?php

namespace Event\Service;

use Zend\ServiceManager\ServiceLocatorInterface;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineORMModule\Form\Annotation\AnnotationBuilder;

/*
 * Entities
 */
use Event\Entity\Event;

class eventService implements eventServiceInterface {

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
    public function getEvents() {
        $events = $this->entityManager->getRepository(Event::class)
                ->findBy(['deleted' => 0], ['eventStartDate' => 'DESC']);

        return $events;
    }

    /**
     *
     * Create array of events for google maps 
     *
     * @param       events $events array with events
     * 
     * @return      array
     *
     */
    public function createEventsArrayForMaps($events) {
        $locations = [];
        if ($events != null) {
            foreach ($events AS $event) {
                if (!empty($event->getLatitude()) && !empty($event->getLongitude())) {
                    //Set icon
                    $icon = '/img/icons/google-maps/bullseye.svg';
                    if (is_object($event->getCategory()) && is_object($event->getCategory()->getFile())) {
                        $icon = $event->getCategory()->getFile()->getPath();
                    }

                    $location = [];
                    $location[] = $event->getLabelText();
                    $location[] = $event->getLatitude();
                    $location[] = $event->getLongitude();
                    $location[] = $icon;
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
            $endYear = new \DateTime("$year-12-31 23:59:59");
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
     * Get event object
     *
     * @return      object
     *
     */
    public function getEventsByYearAndCategory($year = null, $catgory = null) {

        if ($year != null) {
            if ($year != 'all') {
                $beginYear = new \DateTime("$year-1-1 00:00:00");
                $endYear = new \DateTime("$year-12-31 23:59:59");
            }
            $qb = $this->entityManager->getRepository('Event\Entity\Event')->createQueryBuilder('e');
            $qb->select('e')
                    ->where('e.deleted = 0');
            if ($year != 'all') {
                $qb->andWhere('e.eventStartDate >= :beginOfYear')
                        ->andWhere('e.eventEndDate <= :endOfYear');
            }

            if ($catgory != 'all' && $catgory != null) {
                $qb->andWhere('e.category = :category');
            }
            $qb->orderBy('e.eventStartDate', 'DESC');
            if ($year != 'all') {
                $qb->setParameter('beginOfYear', $beginYear)
                        ->setParameter('endOfYear', $endYear);
            }
            if ($catgory != 'all' && $catgory != null) {
                $qb->setParameter('category', $catgory);
            }

            $query = $qb->getQuery();
            $result = $query->getResult();

            return $result;
        } else {
            return null;
        }
    }

    /**
     *
     * Get array of startdates grouped by year
     *
     * @return      array
     *
     */
    public function getYearsOfEvents() {
        $qb = $this->entityManager->getRepository('Event\Entity\Event')
                ->createQueryBuilder('e')
                ->select('YEAR(e.eventStartDate) AS eYear')
                ->where('e.deleted = 0')
                ->groupBy('eYear');

        $query = $qb->getQuery();
        $result = $query->getResult();
        return $result;
    }

    /**
     *
     * Get array of archived events 
     *
     * @return      array
     *
     */
    public function getArchivedEvents() {
        $events = $this->entityManager->getRepository(Event::class)
                ->findBy(['deleted' => 1], ['id' => 'ASC']);

        return $events;
    }

    /**
     *
     * Get object of an event by id
     *
     * @param       id $id of the event
     * @return      object
     *
     */
    public function getEventById($id) {
        $event = $this->entityManager->getRepository(Event::class)
                ->findOneBy(['id' => $id], []);

        return $event;
    }

    /**
     *
     * Create a new Event object
     *
     * @return      object
     *
     */
    public function createEvent() {
        return new Event();
    }

    /**
     *
     * Delete a Event object from database
     * @param       event $event object
     * @return      object
     *
     */
    public function deleteEvent($event) {
        $this->entityManager->remove($event);
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
    public function setNewEvent($event, $currentUser) {
        $event->setDateCreated(new \DateTime());
        $event->setCreatedBy($currentUser);

        $this->storeEvent($event);
    }

    /**
     *
     * Set data to existing event
     *
     * @param       event $event object
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function setExistingEvent($event, $currentUser) {
        $event->setDateChanged(new \DateTime());
        $event->setChangedBy($currentUser);
        $this->storeEvent($event);
    }

    /**
     *
     * Archive event
     *
     * @param       event $event object
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function archiveEvent($event, $currentUser) {
        $event->setDateDeleted(new \DateTime());
        $event->setDeleted(1);
        $event->setDeletedBy($currentUser);

        $this->storeEvent($event);
    }

    /**
     *
     * UnArchive event
     *
     * @param       event $event object
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function unArchiveEvent($event, $currentUser) {
        $event->setDeletedBy(NULL);
        $event->setChangedBy($currentUser);
        $event->setDeleted(0);
        $event->setDateDeleted(NULL);
        $event->setDateChanged(new \DateTime());

        $this->storeEvent($event);
    }

    /**
     *
     * Save event to database
     *
     * @param       event object
     * @return      void
     *
     */
    public function storeEvent($event) {
        $this->entityManager->persist($event);
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
    public function createEventForm($event) {
        $builder = new AnnotationBuilder($this->entityManager);
        $form = $builder->createForm($event);
        $form->setHydrator(new DoctrineHydrator($this->entityManager, 'Event\Entity\Event'));
        $form->bind($event);

        return $form;
    }

}
