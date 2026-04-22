<?php
namespace w3des\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use w3des\AdminBundle\Model\ValueTrait;
use w3des\AdminBundle\Model\ValueInterface;

/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class NodeVariable implements ValueInterface
{
    use ValueTrait;

    /**
     * @ORM\ManyToOne(targetEntity="w3des\AdminBundle\Entity\Node", inversedBy="variables")
     * @ORM\JoinColumn(name="node_id", onDelete="CASCADE")
     * @ORM\Id
     */
    protected $node;

    /**
     * @ORM\Column(type="string")
     * @ORM\Id
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=2)
     * @ORM\Id
     */
    protected $locale;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    protected $pos = 0;

    public function getNode()
    {
        return $this->node;
    }

    public function setNode($node)
    {
        $this->node = $node;
        return $this;
    }
}

