<?php
namespace w3des\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Range;

/**
 * @ORM\Entity()
 * @ORM\Table(name="rate_value")
 */
class RateValue
{

    /**
     * @ORM\ManyToOne(targetEntity="Rate", inversedBy="values")
     * @ORM\JoinColumn(name="rate_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @ORM\Id
     */
    private $rate;

    /**
     * @ORM\ManyToOne(targetEntity="RateCategory")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @ORM\Id
     */
    private $category;

    /**
     * @ORM\Column(type="integer")
     * @Range(min=0, max=5)
     */
    private $value;

    public function getRate()
    {
        return $this->rate;
    }

    public function setRate($rate)
    {
        $this->rate = $rate;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($category)
    {
        $this->category = $category;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }
}