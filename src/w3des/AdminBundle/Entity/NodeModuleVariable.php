<?php
namespace w3des\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use w3des\AdminBundle\Model\ValueInterface;
use w3des\AdminBundle\Model\ValueTrait;

/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class NodeModuleVariable implements ValueInterface
{
    use ValueTrait;

    /**
     * @ORM\ManyToOne(targetEntity="w3des\AdminBundle\Entity\NodeModule", inversedBy="variables")
     * @ORM\JoinColumn(name="node_module_id", onDelete="CASCADE")
     * @ORM\Id
     */
    protected $module;

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

    public function getModule()
    {
        return $this->module;
    }

    public function setModule($module)
    {
        $this->module = $module;
        return $this;
    }
}

