<?php
namespace w3des\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class NodeModule
{

    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="w3des\AdminBundle\Entity\Node", inversedBy="modules")
     * @ORM\JoinColumn(name="node_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $node;

    /**
     * @ORM\Column(type="string")
     */
    protected $section;

    /**
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @ORM\Column(type="integer")
     */
    protected $pos;

    /**
     * @ORM\OneToMany(targetEntity="w3des\AdminBundle\Entity\NodeModuleVariable", mappedBy="module", cascade={"all"})
     * @ORM\OrderBy({"name" = "asc", "locale" = "asc", "pos" = "asc"})
     */
    protected $variables;

    /**
     * @ORM\OneToMany(targetEntity="Node", orphanRemoval=true, mappedBy="rootModule", cascade={"all"})
     * @ORM\OrderBy({"pos" = "ASC"})
     */
    protected $children;

    protected $fields;

    public function __construct()
    {
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

    public function getNode()
    {
        return $this->node;
    }

    public function setNode($node)
    {
        $this->node = $node;
        return $this;
    }

    public function getSection()
    {
        return $this->section;
    }

    public function setSection($section)
    {
        $this->section = $section;
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

    public function getPos()
    {
        return $this->pos;
    }

    public function setPos($pos)
    {
        $this->pos = $pos;
        return $this;
    }

    public function getVariables()
    {
        return $this->variables;
    }

    public function setVariables($variables)
    {
        $this->variables = $variables;
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

    public function getFields()
    {
        return $this->fields;
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
        return $this;
    }

}
