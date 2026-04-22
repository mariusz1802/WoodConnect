<?php
namespace w3des\AdminBundle\Model;

class ValueList implements \ArrayAccess
{

    /**
     * @var Value[]
     */
    protected $values = [];

    /**
     * @var ValueDefinition[]
     */
    protected $definitions;

    protected $locales = [];

    protected $models = [];

    public function __construct(array $locales, array $definitions)
    {
        $this->definitions = $definitions;
        $this->locales = $locales;
        foreach ($this->definitions as $def) {
            if ($def->locale) {
                foreach ($this->locales as $loc) {
                    $this->values[$def->name . '_' . $loc] = new Value($def, $loc);
                }
            } else {
                $this->values[$def->name] = new Value($def, '');
            }
        }
    }

    public function __get($name)
    {
        return $this->values[$name]->getValue();
    }

    /**
     * @param string $name
     * @throws \InvalidArgumentException
     * @return \w3des\AdminBundle\Model\ValueDefinition
     */
    public function getDefinition($name)
    {
        if (! $this->hasDefinition($name)) {
            throw new \InvalidArgumentException('Definition not exists');
        }
        return $this->definitions[$name];
    }

    public function hasDefinition($name)
    {
        return isset($this->definitions[$name]);
    }

    public function set($name, $locale, $value)
    {
        if ($this->getDefinition($name)->locale) {
            $this->{$name . '_' . $locale} = $value;
        } else {
            $this->{$name} = $value;
        }
    }

    public function __set($name, $v)
    {
        $this->values[$name]->setValue($v);
    }

    public function fromArray(array $vals)
    {
        foreach ($vals as $k => $v) {
            $this->{$k} = $v;
        }
    }

    public function toArray()
    {
        $res = [];

        foreach ($this->definitions as $name => $v) {
            if ($v->locale) {
                foreach ($this->locales as $loc) {
                    $res[$name . '_' . $loc] = $this->{$name . '_' . $loc};
                }
            } else {
                $res[$name] = $this->{$name};
            }
        }

        return $res;
    }

    /**
     * @param ValueInterface[] $models
     */
    public function loadModels($models)
    {
        $tmp = [];
        $res = [];
        foreach ($models as $item) {
            if (! $this->hasDefinition($item->getName())) {
                continue;
            }
            $def = $this->getDefinition($item->getName());
            $index = $def->name;

            if ($def->locale) {
                $index .= '_' . $item->getLocale();
            }
            $res[$index][] = $item;
            if ($def->array) {
                if (! isset($tmp[$index])) {
                    $tmp[$index] = [];
                }
                $tmp[$index][$item->getPos()] = $item->getValue();
            } else {
                $tmp[$index] = $item->getValue();
            }
        }
        foreach ($tmp as $name => $val) {
            $this->values[$name]->setValue($val, true);
        }

        $this->models = $res;
    }

    /**
     * @return \w3des\AdminBundle\Model\Value[]
     */
    public function getValues()
    {
        return $this->values;
    }

    public function getDefinitions()
    {
        return $this->definitions;
    }

    public function getLocales()
    {
        return $this->locales;
    }

    public function getModels()
    {
        return $this->models;
    }

    /**
     * @param $offset
     */
    public function offsetExists($offset)
    {
        return isset($this->values[$offset]);
    }

    /**
     * @param $offset
     */
    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    /**
     * @param $offset
     * @param $value
     */
    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    /**
     * @param $offset
     */
    public function offsetUnset($offset)
    {
        $this->{$offset} = null;
    }
}

