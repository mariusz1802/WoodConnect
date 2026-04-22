<?php
namespace w3des\NewsletterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="newsletter_email", uniqueConstraints={@ORM\UniqueConstraint(name="newsletetr_email_uniq", columns={"email", "locale"})})
 * @ORM\HasLifecycleCallbacks()
 */
class NewsletterEmail
{

    /**
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=2)
     */
    protected $locale;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="string", name="token")
     * @ORM\Id
     */
    protected $id;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
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

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
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

    public function getId()
    {
        return $this->id;
    }

    public function setId($token)
    {
        $this->id = $token;
        return $this;
    }
}

