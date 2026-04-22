<?php
namespace w3des\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class RateCategoryType extends AbstractType
{

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, ['label' => 'Nazwa']);
        $builder->add('position', NumberType::class, ['label' => 'Pozycja']);
    }
}

