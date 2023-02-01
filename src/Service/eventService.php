<?php

namespace Event\Service;

use Laminas\Form\Annotation\AnnotationBuilder;
use Laminas\ServiceManager\ServiceLocatorInterface;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Laminas\Paginator\Paginator;

/*
 * Entities
 */

use Event\Entity\Event;

class eventService implements eventServiceInterface
{

    /**
     * Constructor.
     */
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Get array of events
     * @return      query
     */
    public function getEvents()
    {
        $qb = $this->entityManager->getRepository(Event::class)->createQueryBuilder('e')
            ->where('e.deleted = 0')
            ->orderBy('e.eventStartDate', 'DESC');
        return $qb->getQuery();
    }

    /**
     *
     * Get array of languages  for pagination
     * @var $query query
     * @var $currentPage current page
     * @var $itemsPerPage items on a page
     *
     * @return      array
     *
     */
    public function getItemsForPagination($query, $currentPage = 1, $itemsPerPage = 10)
    {
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage($itemsPerPage);
        $paginator->setCurrentPageNumber($currentPage);
        return $paginator;
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
    public function createEventsArrayForMaps($events)
    {
        $locations = [];
        if ($events != null) {
            foreach ($events AS $event) {
                if (!empty($event->getLatitude()) && !empty($event->getLongitude())) {
                    //Set icon
                    $icon = '/img/icons/google-maps/bullseye.svg';
                    if (is_object($event->getCategory()) && is_object($event->getCategory()->getFile())) {
                        $icon = $event->getCategory()->getFile()->getPath();
                    }
                    $startDateEvent = $event->getEventStartDate()->format('Y-m-d');
                    $eventTitle = $event->getTitle();
                    $location = [];
                    $location[] = '<h6>' . $eventTitle . '</h6><b>' . $startDateEvent . '</b></br>' . $event->getLabelText();
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
    public function getUpcommingEvent()
    {

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
    public function getEventsByYear($year = null)
    {
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
     * Get array of customers
     * @var $searchString string to search for
     *
     * @return      array
     *
     */
    public function searchEvents($searchString)
    {
        $qb = $this->entityManager->getRepository(Event::class)->createQueryBuilder('e');
        $orX = $qb->expr()->orX();
        $orX->add($qb->expr()->like('e.title', $qb->expr()->literal("%$searchString%")));
        $orX->add($qb->expr()->like('e.labelText', $qb->expr()->literal("%$searchString%")));
        $orX->add($qb->expr()->like('e.text', $qb->expr()->literal("%$searchString%")));
        $qb->where($orX);
        $qb->orderBy('e.eventStartDate', 'DESC');
        return $qb->getQuery();
    }

    /**
     *
     * Get event object
     *
     * @return      object
     *
     */
    public function getEventsByYearAndCategory($year = null, $catgory = null, $array = false)
    {

        if ($year != null) {
            if ($year != 'all') {
                $beginYear = new \DateTime("$year-1-1 00:00:00");
                $endYear = new \DateTime("$year-12-31 23:59:59");
            }
            $qb = $this->entityManager->getRepository('Event\Entity\Event')->createQueryBuilder('e');
            $qb->select('e', 'ei', 'it');
            $qb->join('e.eventImage', 'ei')
                ->join('ei.imageTypes', 'it')
                ->where('e.deleted = 0');
            //$qb->andWhere('it.imageTypeName like "400x200"');
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

            if($array) {
                $result = $query->getArrayResult();
            } else {
                $result = $query->getResult();
            }
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
    public function getYearsOfEvents()
    {
        $qb = $this->entityManager->getRepository('Event\Entity\Event')
            ->createQueryBuilder('e')
            ->select('YEAR(e.eventStartDate) AS eYear')
            ->where('e.deleted = 0')
            ->groupBy('eYear')
            ->orderBy('eYear', 'DESC');

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
    public function getArchivedEvents()
    {
        $qb = $this->entityManager->getRepository(Event::class)->createQueryBuilder('e')
            ->where('e.deleted = 1')
            ->orderBy('e.eventStartDate', 'DESC');
        return $qb->getQuery();
    }

    /**
     *
     * Get object of an event by id
     *
     * @param       id $id of the event
     * @return      object
     *
     */
    public function getEventById($id)
    {
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
    public function createEvent()
    {
        return new Event();
    }

    /**
     *
     * Delete a Event object from database
     * @param       event $event object
     * @return      object
     *
     */
    public function deleteEvent($event)
    {
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
    public function setNewEvent($event, $currentUser)
    {
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
    public function setExistingEvent($event, $currentUser)
    {
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
    public function archiveEvent($event, $currentUser)
    {
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
    public function unArchiveEvent($event, $currentUser)
    {
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
    public function storeEvent($event)
    {
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
    public function createEventForm($event)
    {
        $builder = new AnnotationBuilder($this->entityManager);
        $form = $builder->createForm($event);
        $form->setHydrator(new DoctrineHydrator($this->entityManager, 'Event\Entity\Event'));
        $form->bind($event);

        return $form;
    }

}
