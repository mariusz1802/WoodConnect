<?php
namespace w3des\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType as BasicFileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class AdvFileType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', BasicFileType::class, [
            'label' => 'Wgraj nowy / zastąp'
        ]);
        $builder->add('remove', CheckboxType::class,[
            'label' => 'Usuń plik'
        ]);
        $builder->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event) use($builder) {
            if (empty($event->getData()['path'])) {
                $event->getForm()->remove('remove');
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('dir', 'settings');
    }
}

