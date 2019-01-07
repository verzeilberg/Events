<?php

namespace Event\Service;

interface eventCategoryServiceInterface {

    /**
     *
     * Get array of events 
     *
     * @return      array
     *
     */
    public function getEventCategories();

    /**
     *
     * Get array of archived event ctegories
     *
     * @return      array
     *
     */
    public function getArchivedEventCategories();

    /**
     *
     * Get object of an event category by id
     *
     * @param       id $id of the event category
     * @return      object
     *
     */
    public function getEventCategoryById($id);

    /**
     *
     * Create a new Event object
     *
     * @return      object
     *
     */
    public function createEventCategory();

    /**
     *
     * Delete a Event category object from database
     * @param       event $eventCategory object
     * @return      object
     *
     */
    public function deleteEventCategory($eventCategory);

    /**
     *
     * Set data to new event
     *
     * @param       event $event object
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function setNewEventCategory($eventCategory, $currentUser);

    /**
     *
     * Set data to existing event category
     *
     * @param       event $eventCategory object
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function setExistingEventCategory($event, $currentUser);

    /**
     *
     * Archive event category
     *
     * @param       event $eventCategory object
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function archiveEventCategory($eventCategory, $currentUser);

    /**
     *
     * UnArchive event category
     *
     * @param       event $eventCategory object
     * @param       currentUser $currentUser whos is logged on
     * @return      void
     *
     */
    public function unArchiveEventCategory($event, $currentUser);

    /**
     *
     * Save event to database
     *
     * @param       event object
     * @return      void
     *
     */
    public function storeEventCategory($eventCategory);

    /**
     *
     * Create form of an object
     *
     * @param       event $event object
     * @return      form
     *
     */
    public function createEventCategoryForm($event);

    /**
     *
     * Get event object
     *
     * @return      object
     *
     */
    public function getUpcommingEvent();
}
