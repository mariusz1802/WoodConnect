<?php
namespace w3des\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class RecordsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, [
            'label' =>'Imię i nazwisko',
            'required' => true
        ]);

        $builder->add('practiceName', TextType::class, [
            'label' =>'Nazwa i adres praktyki',
            'required' => true
        ]);
        $builder->add('date', TextType::class, [
            'label' =>'Data szkolenia',
            'required' => true
        ]);

        $builder->add('trainingName', TextType::class, [
            'label' =>'Nazwa szkolenia',
            'required' => true
        ]);
        $builder->add('phone', TextType::class, [
            'label' => 'Telefon'
        ]);
        $builder->add('email', EmailType::class, [
            'label' => 'E-mail',
            'required' => true
        ]);
    }

    public function getParent()
    {
        return FormType::class;
    }
}

