<?php

namespace Event\Entity;

use Doctrine\ORM\Mapping as ORM;
use Laminas\Form\Annotation;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Model\UnityOfWork;

/**
 * This class represents a event item.
 * @ORM\Entity()
 * @ORM\Table(name="events")
 */
class Event extends UnityOfWork {

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", length=11)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="event_start_date", type="datetime", nullable=false)
     * @Annotation\Options({
     * "label": "Startdatum event",
     * "label_attributes": {"class": "control-label"}
     * })
     * @Annotation\Attributes({"class":"form-control", "readonly":"readonly"})
     */
    protected $eventStartDate;

    /**
     * @ORM\Column(name="event_end_date", type="datetime", nullable=false)
     * @Annotation\Options({
     * "label": "Einddatum event",
     * "label_attributes": {"class": "control-label"}
     * })
     * @Annotation\Attributes({"class":"form-control", "readonly":"readonly"})
     */
    protected $eventEndDate;

    /**
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     * @Annotation\Options({
     * "label": "Titel",
     * "label_attributes": {"class": "control-label"}
     * })
     * @Annotation\Attributes({"class":"form-control", "placeholder":"Titel"})
     */
    protected $title;

    /**
     * Many Events have One Image.
     * @ORM\ManyToOne(targetEntity="UploadImages\Entity\Image")
     * @ORM\JoinColumn(name="imageId", referencedColumnName="id", onDelete="SET NULL")
     */
    private $eventImage;

    /**
     * @ORM\Column(name="longitude", type="text", length=255, nullable=true)
     * @Annotation\Options({
     * "label": "Longitude",
     * "label_attributes": {"class": "col-sm-2 col-md-2 col-lg-2 control-label"}
     * })
     * @Annotation\Attributes({"class":"form-control", "id":"longclicked"})
     */
    protected $longitude;

    /**
     * @ORM\Column(name="latitude", type="text", length=255, nullable=true)
     * @Annotation\Options({
     * "label": "Latitude",
     * "label_attributes": {"class": "col-sm-2 col-md-2 col-lg-2 control-label"}
     * })
     * @Annotation\Attributes({"class":"form-control", "id":"latclicked"})
     */
    protected $latitude;

    /**
     * @ORM\Column(name="label_text", type="text", nullable=true)
     * @Annotation\Options({
     * "label": "Label text",
     * "label_attributes": {"class": "control-label"}
     * })
     * @Annotation\Attributes({"class":"form-control", "id":"editor1"})
     */
    protected $labelText;

    /**
     * @ORM\Column(name="text", type="text", nullable=true)
     * @Annotation\Options({
     * "label": "Text",
     * "label_attributes": {"class": "control-label"}
     * })
     * @Annotation\Attributes({"class":"form-control", "id":"editor2"})
     */
    protected $text;

    /**
     * Many Categories have One Event.
     * @ORM\ManyToOne(targetEntity="EventCategory", inversedBy="events")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * @Annotation\Type("DoctrineModule\Form\Element\ObjectSelect")
     * @Annotation\Options({
     * "empty_option": "---",
     * "target_class":"Event\Entity\EventCategory",
     * "property": "name",
     * "label": "Categorie",
     * "label_attributes": {"class": "control-label"},
     * "find_method":{"name": "findBy","params": {"criteria":{"deleted": "0"},"orderBy":{"name": "ASC"}}}
     * })
     * @Annotation\Attributes({"class":"form-control"})
     */
    private $category;

    function getId() {
        return $this->id;
    }

    function getEventStartDate() {
        return $this->eventStartDate;
    }

    function getEventEndDate() {
        return $this->eventEndDate;
    }

    function getTitle() {
        return $this->title;
    }

    function getEventImage() {
        return $this->eventImage;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setEventStartDate($eventStartDate) {
        $this->eventStartDate = $eventStartDate;
    }

    function setEventEndDate($eventEndDate) {
        $this->eventEndDate = $eventEndDate;
    }

    function setTitle($title) {
        $this->title = $title;
    }

    function setEventImage($eventImage) {
        $this->eventImage = $eventImage;
    }

    function getLongitude() {
        return $this->longitude;
    }

    function getLatitude() {
        return $this->latitude;
    }

    function setLongitude($longitude) {
        $this->longitude = $longitude;
    }

    function setLatitude($latitude) {
        $this->latitude = $latitude;
    }

    function getLabelText() {
        return $this->labelText;
    }

    function setLabelText($labelText) {
        $this->labelText = $labelText;
    }

    function getCategory() {
        return $this->category;
    }

    function setCategory($category) {
        $this->category = $category;
    }
    
    function getText() {
        return $this->text;
    }

    function setText($text) {
        $this->text = $text;
    }



}
