<?php

namespace Event\Entity;

use Doctrine\ORM\Mapping as ORM;
use Laminas\Form\Annotation;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Model\UnityOfWork;

/**
 * 
 * event categoryClub
 *
 * @ORM\Entity
 * @ORM\Table(name="event_categories")
 */
class EventCategory extends UnityOfWork {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", length=11, name="id");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * * @Annotation\Options({
     * "label": "Titel",
     * "label_attributes": {"class": "col-sm-1 col-md-1 col-lg-1 control-label"}
     * })
     * @Annotation\Attributes({"class":"form-control", "placeholder":"titel"})
     */
    protected $name;

    /**
     * @ORM\Column(type="text", length=255, nullable=true)
     * * @Annotation\Options({
     * "label": "Omschrijving",
     * "label_attributes": {"class": "col-sm-1 col-md-1 col-lg-1 control-label"}
     * })
     * @Annotation\Attributes({"class":"form-control", "placeholder":"Omschrijving", "id":"editor"})
     */
    protected $description;

    /**
     * One Event Category has One File.
     * @ORM\OneToOne(targetEntity="UploadFiles\Entity\UploadFiles")
     * @ORM\JoinColumn(name="file_id", referencedColumnName="id")
     */
    private $file;

    /**
     * One Event category has Many Events.
     * @ORM\OneToMany(targetEntity="Event", mappedBy="category")
     */
    private $events;

    public function __construct() {
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function addEvents($events) {
        foreach ($events as $event) {
            $this->events->add($event);
        }
    }

    public function removeEvents($events) {
        foreach ($events as $event) {
            $this->events->removeElement($event);
        }
    }

    function getId() {
        return $this->id;
    }

    function getName() {
        return $this->name;
    }

    function getDescription() {
        return $this->description;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setDescription($description) {
        $this->description = $description;
    }

    function getEvents() {
        return $this->events;
    }

    function setEvents($events) {
        $this->events = $events;
    }
    
    function getFile() {
        return $this->file;
    }

    function setFile($file) {
        $this->file = $file;
    }



}
