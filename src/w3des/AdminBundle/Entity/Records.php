<?php
namespace w3des\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table()
 */
class Records
{

    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(groups={"default"})
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Assert\NotBlank(groups={"default"})
     */
    private $practiceName;

    /**
     * @ORM\Column(name="training_date", type="text", nullable=true)
     * @Assert\NotBlank(groups={"training"})
     */
    private $date;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(groups={"default"})
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(groups={"default"})
     */
    private $trainingName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $kind;

    /**
     * @ORM\Column(type="text",nullable=true)
     * @Assert\NotBlank(groups={"cad_cam"})
     */
    private $package;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getPracticeName()
    {
        return $this->practiceName;
    }

    public function setPracticeName($practiceName)
    {
        $this->practiceName = $practiceName;
        return $this;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function getTrainingName()
    {
        return $this->trainingName;
    }

    public function setTrainingName($trainingName)
    {
        $this->trainingName = $trainingName;
        return $this;
    }
    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function getKind()
    {
        return $this->kind;
    }

    public function setKind($kind)
    {
        $this->kind = $kind;
    }

    public function getPackage()
    {
        return $this->package;
    }

    public function setPackage($package)
    {
        $this->package = $package;
    }


}

