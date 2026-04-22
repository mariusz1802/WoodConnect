<?php
namespace w3des\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * @ORM\Entity(repositoryClass="w3des\AdminBundle\Repository\UserRepository")
 * @ORM\Table(name="admin_users")
 */
class User implements UserInterface, AdvancedUserInterface, \Serializable
{

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json_array", name="main_roles")
     */
    private $mainRoles = [];

    /**
     * @ORM\Column(name="is_enabled", type="boolean")
     */
    private $isEnabled;

    public function __construct()
    {
        $this->isEnabled = true;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoles()
    {
        $roles = $this->getMainRoles();
        $roles = \is_array($roles) ? $roles : [];
        $roles[] = 'ROLE_USER';
        $roles[] = 'ROLE_ADMIN';


        return $roles;
    }

    /**
     * {@inheritDoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * {@inheritDoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * {@inheritDoc}
     */
    public function eraseCredentials()
    {}

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = strtolower($email);
        return $this;
    }

    public function getMainRoles()
    {
        return $this->mainRoles;
    }

    public function setMainRoles($mainRoles)
    {
        $this->mainRoles = $mainRoles;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    public function setEnabled($enabled)
    {
        $this->isEnabled = $enabled;
        return $this;
    }

    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->email,
            $this->password,
            $this->isEnabled,
            $this->mainRoles
        ));
    }

    public function unserialize($serialized)
    {
        list ($this->id, $this->email, $this->password, $this->isEnabled, $this->mainRoles) = unserialize($serialized);
    }
}

