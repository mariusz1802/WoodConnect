<?php
namespace w3des\AdminBundle\Form\Type;

use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactType extends AbstractType
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
        $builder->add('phone', TextType::class, [
            'label' => 'Telefon'
        ]);
        $builder->add('email', EmailType::class, [
            'label' => 'E-mail'
        ]);
        $builder->add('subject', TextType::class, [
            'label' => 'Miasto',
            'required' => false
        ]);
        $builder->add('content', TextareaType::class, [
            'label' => 'Wiadomość'
        ]);
        $cnt = new IsTrue();
        $cnt->message = 'Niepoprawna wartość captcha.';
        $builder->add('recaptcha', EWZRecaptchaType::class, [
            'mapped' => false,
            'required' => true,
            'constraints' => array(
                $cnt
            ),
            'attr' => [
                'options' => [
                    'theme' => 'light',
                    'type' => 'image',
                    'size' => 'normal',
                    'callback' => null,
                    'expiredCallback' => null,
                    'bind' => null,
                    'defer' => false,
                    'async' => false,
                ]
            ]
        ]);
    }

    public function getParent()
    {
        return FormType::class;
    }
}

