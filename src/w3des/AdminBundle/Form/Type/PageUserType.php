<?php
namespace w3des\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PageUserType extends AbstractType
{

    /**
     * {@inheritDoc}
     */
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstName', TextType::class);
        $builder->add('lastName', TextType::class);
        $builder->add('phone', TextType::class);
        $builder->add('email', EmailType::class);
        $builder->add('plainPassword', RepeatedType::class, [
            'mapped' => false,
            'first_options' => [
                'label' => 'Hasło'
            ],
            'required' => $builder->getData()->getId() ? false:true,
            'second_options' => [
                'label' => 'Powtórz'
            ],
            'type' => PasswordType::class
        ]);
        $builder->add('isEnabled', CheckboxType::class, [
            'label' => 'Włączony'
        ]);
    }
}

