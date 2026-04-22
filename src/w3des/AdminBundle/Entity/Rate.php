<?php
namespace w3des\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\JoinColumn;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * @ORM\Entity()
 * @ORM\Table(name="rate", indexes={
 *  @ORM\Index(name="rate_view", columns={"approved", "confirmed", "created_at"})
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class Rate
{

    /**
     * @ORM\Column(type="guid")
     * @ORM\Id
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Email()
     * @NotBlank()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ip;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $approved = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $confirmed = false;

    /**
     * @ORM\Column(type="decimal", scale=2, precision=5)
     */
    private $value;

    /**
     * @ORM\OneToMany(targetEntity="RateValue", mappedBy="rate", cascade="all")
     * @Valid()
     */
    private $values;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    public function __construct()
    {
        $this->values = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->id = Uuid::uuid4()->toString();
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preSave()
    {
        $this->value = 0;
        foreach ($this->values as $v) {
            $this->value += $v->getValue();
        }
        $this->value /= $this->values->count();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getApproved()
    {
        return $this->approved;
    }

    public function setApproved($approved)
    {
        $this->approved = $approved;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function setValues($values)
    {
        $this->values = $values;
    }
    public function getConfirmed()
    {
        return $this->confirmed;
    }

    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;
    }



}