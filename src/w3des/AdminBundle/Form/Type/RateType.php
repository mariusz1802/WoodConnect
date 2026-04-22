<?php
namespace w3des\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class RateType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, [
            'label' => 'Imię i Nazwisko / Nazwa gabinetu',
            'required' => true
        ]);

        $builder->add('email', EmailType::class, [
            'label' => 'E-mail',
            'required' => true
        ]);

        $builder->add('comment', TextareaType::class, [
            'label' => 'Wiadomość',
            'required' => false
        ]);
        foreach ($builder->getData()->getValues() as $k => $v) {
            $builder->add('val_' . $k, $options['admin'] ? NumberType::class : HiddenType::class, [
                'label' => $v->getCategory()
                    ->getName(),
                'property_path' => 'values[' . $k . '].value',
                'required' => false
            ]);
        }
        if ($options['admin']) {
            $builder->add('confirmed', CheckboxType::class, [
                'label' => 'Potwierdzony',
                'required'=>false
            ]);
            $builder->add('approved', CheckboxType::class, [
                'label' => 'Zatwierdzony',
                'required'=>false
            ]);
        }
    }

    public function getParent()
    {
        return FormType::class;
    }

    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
    {
        $resolver->setDefault('admin', false);
    }
}

