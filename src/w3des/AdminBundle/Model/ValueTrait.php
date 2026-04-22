<?php
namespace w3des\AdminBundle\Model;

use Doctrine\ORM\Mapping as ORM;

trait ValueTrait {

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=2)
     */
    protected $locale;

    /**
     * @ORM\Column(type="integer")
     */
    protected $pos = 0;

    /**
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $stringValue;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $intValue;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $floatValue;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $textValue;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $dateTimeValue;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $width;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $height;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $mime;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $size;

    public function getPos()
    {
        return $this->pos;
    }

    public function setPos($pos)
    {
        $this->pos = $pos;
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

    public function cleanValues()
    {
        $this->intValue = null;
        $this->stringValue = null;
        $this->textValue = null;
        $this->floatValue = null;
        $this->dateTimeValue = null;
        $this->height = null;
        $this->width = null;
        $this->size = null;
        $this->mime = null;
    }

    public function getStringValue()
    {
        return $this->stringValue;
    }

    public function setStringValue($stringValue)
    {
        $this->cleanValues();
        $this->stringValue = $stringValue;
        return $this;
    }

    public function getIntValue()
    {
        return $this->intValue;
    }

    public function setIntValue($intValue)
    {
        $this->cleanValues();
        $this->intValue = $intValue;
        return $this;
    }

    public function getFloatValue()
    {
        return $this->floatValue;
    }

    public function setFloatValue($floatValue)
    {
        $this->cleanValues();
        $this->floatValue = $floatValue;
        return $this;
    }

    public function getTextValue()
    {
        return $this->textValue;
    }

    public function setTextValue($textValue)
    {
        $this->cleanValues();
        $this->textValue = $textValue;
        return $this;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    public function getMime()
    {
        return $this->mime;
    }

    public function setMime($mime)
    {
        $this->mime = $mime;
        return $this;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
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

    public function getDateTimeValue()
    {
        return $this->dateTimeValue;
    }

    public function setDateTimeValue($dateTimeValue)
    {
        $this->dateTimeValue = $dateTimeValue;
        return $this;
    }

    public function getValue()
    {
        switch ($this->getType()) {
            /** @var \w3des\AdminBundle\Entity\Setting $sett */
            case 'string':
                return $this->getStringValue();
            case 'text':
                return $this->getTextValue();
            case 'datetime':
                return $this->getDateTimeValue();
            case 'bool':
                return (bool) $this->getIntValue();
            case 'integer':
                return $this->getIntValue();
            case 'float':
                return $this->getFloatValue();
            case 'file':
                return [
                    'path' => $this->getStringValue(),
                    'size' => $this->getSize(),
                    'mime' => $this->getMime(),
                    'width' => $this->getWidth(),
                    'height' => $this->getHeight()
                ];
        }
    }

    public static function getFieldName($storeType)
    {
        switch ($storeType) {
            /** @var \w3des\AdminBundle\Entity\Setting $sett */
            case 'string':
                return 'stringValue';
            case 'text':
                return 'textValue';
            case 'datetime':
                return 'dateTimeValue';
            case 'bool':
                return 'intValue';
            case 'integer':
                return 'intValue';
            case 'float':
                return 'floatValue';
            case 'file':
                return 'stringValue';
        }
    }
}

