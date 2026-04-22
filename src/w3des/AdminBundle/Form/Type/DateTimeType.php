<?php
namespace w3des\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;

class DateTimeType extends AbstractType
{

    public function getParent()
    {
        return \Symfony\Component\Form\Extension\Core\Type\DateTimeType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'widget' => 'single_text',
            'format' => 'yyy-MM-dd hh:mm:ss',
            'attr' => [
                'class' => 'datetime'
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new CallbackTransformer(function ($v) {
            if ($v == 'now') {
                return new \DateTime();
            }
            return $v;
        }, function ($v) {
            return $v;
        }));
    }
}

