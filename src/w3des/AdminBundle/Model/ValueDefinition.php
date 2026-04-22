<?php
namespace w3des\AdminBundle\Model;

use w3des\AdminBundle\Util\ValueTypeDecoder;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Definition for ValueInterface
 */
class ValueDefinition implements \Serializable
{

    public $name;

    public $locale = '';

    public $type;

    public $storeType;

    public $array = false;

    public $options = [];

    public $index = false;

    public $default = null;


    public function __construct($name, $options = [])
    {
        $this->name = $name;
        $this->locale = isset($options['locale']) ? $options['locale'] : '';
        $this->type = isset($options['type']) ? $options['type'] : TextType::class;
        $this->storeType = isset($options['storeType']) ? $options['storeType'] : ValueTypeDecoder::decode($this->type);
        $this->array = isset($options['array']) ? $options['array'] : false;
        $this->options = isset($options['options']) ? $options['options']: [];
        $this->index = isset($options['index']) ? $options['index']: false;
        $this->default = isset($options['default']) ? $options['default']: null;
    }



    public function serialize()
    {
        return serialize($this->name, $this->locale, $this->type, $this->storeType, $this->array, $this->options, $this->default);
    }

    public function unserialize($serialized)
    {
        list ($this->name, $this->locale, $this->type, $this->storeType, $this->array, $this->options, $this->default) = \unserialize($serialized);
    }
}

