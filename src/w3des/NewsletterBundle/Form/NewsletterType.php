<?php
namespace w3des\NewsletterBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class NewsletterType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', TextType::class, [
            'label' => 'Tytuł'
        ]);
        if ($builder->getData()->getId()) {
            $builder->add('test_email', EmailType::class, [
                'required' => false,
                'mapped' => false
            ]);
        }
        $builder->add('content', 'Ivory\CKEditorBundle\Form\Type\CKEditorType', [
            'label' => 'Treść'
        ]);
    }
}

