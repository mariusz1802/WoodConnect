<?php
namespace w3des\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="w3des\AdminBundle\Repository\NodeRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Node
{

    /**
     * @ORM\Id()
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $pos;

    /**
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @ORM\Column(type="string", length=2)
     */
    protected $locale;

    /**
     * @ORM\OneToMany(targetEntity="w3des\AdminBundle\Entity\NodeVariable", mappedBy="node", orphanRemoval=true, fetch="EAGER", cascade={"all"})
     */
    protected $variables;

    /**
     * @ORM\OneToMany(targetEntity="w3des\AdminBundle\Entity\NodeModule", mappedBy="node", orphanRemoval=true, cascade={"all"})
     * @ORM\OrderBy({"section" = "asc", "pos" = "asc"})
     */
    protected $modules;

    /**
     * @ORM\ManyToOne(targetEntity="w3des\AdminBundle\Entity\Node", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", onDelete="CASCADE")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="w3des\AdminBundle\Entity\Node", mappedBy="parent", cascade={"all"})
     * @ORM\OrderBy({"pos" = "ASC"})
     */
    protected $children;

    /**
     * @ORM\ManyToOne(targetEntity="w3des\AdminBundle\Entity\NodeUrl", inversedBy="nodes", cascade={"all"})
     * @ORM\JoinColumn(name="node_url_id", referencedColumnName="id")
     */
    protected $url;

    /**
     * @ORM\ManyToOne(targetEntity="NodeModule", inversedBy="children")
     * @ORM\JoinColumn(onDelete="CASCADE", name="root_module_id")
     */
    protected $rootModule;


    public function __construct()
    {
        $this->modules = new ArrayCollection();
        $this->variables = new ArrayCollection();
        $this->children = new ArrayCollection();
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

    public function getPos()
    {
        return $this->pos;
    }

    public function setPos($pos)
    {
        $this->pos = (int)$pos;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
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

    public function getVariables()
    {
        return $this->variables;
    }

    public function setVariables($variable)
    {
        $this->variables = $variable;
        return $this;
    }

    public function getModules()
    {
        return $this->modules;
    }

    public function setModules($modules)
    {
        $this->modules = $modules;
        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function setChildren($children)
    {
        $this->children = $children;
        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }


    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function bind()
    {
        foreach ($this->getModules() as $mod) {
            $mod->setNode($this);
        }
    }

    public function getRootModule()
    {
        return $this->rootModule;
    }

    public function setRootModule($rootModule)
    {
        $this->rootModule = $rootModule;
        return $this;
    }
    public function findModules($type)
    {
        $tmp = [];
        foreach ($this->modules as $mod) {
            if ($mod->getType() == $type) {
                $tmp[] = $mod;
            }
        }

        return $tmp;
    }


}

