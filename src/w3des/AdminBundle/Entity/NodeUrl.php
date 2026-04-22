<?php
namespace w3des\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(uniqueConstraints={
 *  @ORM\UniqueConstraint(columns={"locale", "slug"})
 * })
 * @ORM\Entity()
 */
class NodeUrl
{

    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="w3des\AdminBundle\Entity\Node", mappedBy="url")
     */
    protected $nodes;

    /**
     * @ORM\Column(type="string", length=2)
     */
    protected $locale;

    /**
     * @ORM\Column(type="string")
     */
    protected $slug;

    /**
     * @ORM\Column(type="text")
     */
    protected $path;

    public function __construct()
    {
        $this->nodes = new ArrayCollection();
    }

    public function getNodes()
    {
        return $this->nodes;
    }

    public function setNodes($nodes)
    {
        $this->nodes = $nodes;

        return $this;
    }

    public function getNode()
    {
        return $this->nodes->count() > 0 ? $this->nodes->first() : null;
    }

    public function setNode($node)
    {
        $this->nodes->clear();
        $this->nodes->add($node);
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

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;
        return $this;
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

}

