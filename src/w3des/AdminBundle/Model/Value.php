<?php
namespace w3des\AdminBundle\Model;

class Value
{

    /**
     * @var ValueDefinition
     */
    protected $definition;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var mixed
     */
    protected $previous;

    /**
     * @var string
     */
    protected $locale;

    public function __construct(ValueDefinition $definition, $locale = '', $value = null)
    {
        $this->definition = $definition;
        $this->value = $value;
        $this->locale = $locale;
    }

    public function getDefinition()
    {
        return $this->definition;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value, $force = true)
    {
        if ($force) {
            $this->previous = $value;
        } else {
            $this->previous = $this->value;
        }
        $this->value = $value;

        return $this;
    }

    public function getLocale()
    {
        return $this->locale;
    }
}

