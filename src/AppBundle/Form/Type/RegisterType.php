<?php
namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class RegisterType extends AbstractType
{

    /**
     * {@inheritDoc}
     */
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstName', TextType::class, ['label' => 'Imię*']);
        $builder->add('lastName', TextType::class, ['label' => 'Nazwisko*']);
        $builder->add('phone', TextType::class, ['label' => 'Telefon*']);
        $builder->add('email', EmailType::class, [
            'required' => false
        ]);
        $builder->add('plainPassword', RepeatedType::class, [
            'required' => false,
            'mapped' => false,
            'first_options' => [
                'label' => 'Hasło'
            ],
            'second_options' => [
                'label' => 'Powtórz'
            ],
            'type' => PasswordType::class
        ]);
    }
}

