<?php
namespace w3des\AdminBundle\Model;

interface ValueInterface
{

    public function cleanValues();

    public function getStringValue();

    public function setStringValue($stringValue);

    public function getIntValue();

    public function setIntValue($intValue);

    public function getFloatValue();

    public function setFloatValue($floatValue);

    public function getTextValue();

    public function setTextValue($textValue);

    public function getWidth();

    public function setWidth($width);

    public function getHeight();

    public function setHeight($height);

    public function getMime();

    public function setMime($mime);

    public function getSize();

    public function setSize($size);

    public function getType();

    public function setType($type);

    public function getLocale();

    public function setLocale($locale);

    public function getName();

    public function setName($name);

    /**
     * @return \DateTime|null
     */
    public function getDateTimeValue();

    public function setDateTimeValue($dateTime);

    public function getPos();

    public function setPos($pos);

    public function getValue();
}

