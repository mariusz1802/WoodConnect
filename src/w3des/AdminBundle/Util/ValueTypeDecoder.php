<?php
namespace w3des\AdminBundle\Util;

use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use w3des\AdminBundle\Form\Type\AdvFileType;
use w3des\AdminBundle\Form\Type\DateTimeType;
use w3des\AdminBundle\Form\Type\NodeType;
use w3des\AdminBundle\Form\Type\UploadedImageType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use AdminBundle\Model\ValueTypeInterface;

class ValueTypeDecoder
{

    protected static $knownTypes = [
        AdvFileType::class => 'file',
        UploadedImageType::class => 'file',
        CKEditorType::class => 'text',
        TextareaType::class => 'text',
        DateTimeType::class => 'datetime',
        \Symfony\Component\Form\Extension\Core\Type\DateTimeType::class => 'datetime',
        NodeType::class => 'integer',
        CheckboxType::class => 'bool',
        TextType::class => 'string'
    ];

    public static function decode($typeName)
    {
        if (isset(self::$knownTypes[$typeName])) {
            return self::$knownTypes[$typeName];
        }
        $type = new \ReflectionClass($typeName);
        if ($type->implementsInterface(ValueTypeInterface::class)) {
            $res = \call_user_func($typeName . '::getStoreType');
            self::$knownTypes[$typeName] = $res;
            return $res;
        }

        return null;
    }
}

