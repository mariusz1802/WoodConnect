<?php
namespace w3des\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use w3des\AdminBundle\Model\ValueInterface;
use w3des\AdminBundle\Model\ValueTrait;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="w3des\AdminBundle\Repository\SettingsRepository")
 */
class Setting implements ValueInterface
{
    use ValueTrait;

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

}

