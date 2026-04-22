<?php
namespace w3des\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

class ContentModuleType extends AbstractType
{

    public function getParent()
    {
        return NodeModuleType::class;
    }
}

