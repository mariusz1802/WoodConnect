<?php
namespace w3des\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GalleryModuleType extends AbstractType
{

    public function getParent()
    {
        return NodeModuleType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('children', GalleryType::class, [
             'label' => false,
             'dir' => $options['dir'],
            'thmb' => $options['thmb'],
            'gallery_type' => $options['gallery_type']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'dir' => 'images',
            'gallery_type' => 'image',
            'thmb' => true
        ]);
    }
}

