<?php

namespace Event\Service;

interface eventServiceInterface {

    /**
     *
     * Get array of events 
     *
     * @return      array
     *
     */
    public function getEvents();

    /**
     *
     * Get array of archived events 
     *
     * @return      array
     *
     */
    public function getArchivedEvents();

    /**
     *
     * Get object of an event by id
     *
     * @param       id $id of the event
     * @return      object
     *
     */
    public function getEventById($id);

    /**
     *
     * Create a new Event object
     *
     * @return      object
     *
     */
    public function createEvent();

    /**
     *
     * Delete a Event object from database
     * @param       event $event object
     * @return      object
     *
     */
    public function deleteEvent($event);

    /**
     *
     * Set data to new event
     *
     * @param       event $event object
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function setNewEvent($event, $currentUser);

    /**
     *
     * Set data to existing event
     *
     * @param       event $event object
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function setExistingEvent($event, $currentUser);

    /**
     *
     * Archive event
     *
     * @param       event $event object
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function archiveEvent($event, $currentUser);

    /**
     *
     * UnArchive event
     *
     * @param       event $event object
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function unArchiveEvent($event, $currentUser);

    /**
     *
     * Save event to database
     *
     * @param       event object
     * @return      void
     *
     */
    public function storeEvent($event);

    /**
     *
     * Create form of an object
     *
     * @param       event $event object
     * @return      form
     *
     */
    public function createEventForm($event);

    /**
     *
     * Get event object
     *
     * @return      object
     *
     */
    public function getUpcommingEvent();
}
