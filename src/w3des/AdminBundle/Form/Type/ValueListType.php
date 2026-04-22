<?php
namespace w3des\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use w3des\AdminBundle\Model\ValueList;

class ValueListType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['inherit_data'] && !$options['value_list']) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $e) use ($options) {
                $this->loadConfig($e->getForm(), $e->getData(), $options);
            });
        } else {
            $this->loadConfig($builder, $options['value_list'] ?: $builder->getData(), $options);
        }
    }

    public function loadConfig($form, ValueList $list, $options)
    {
        $locales = $list->getLocales();
        $definitions = $list->getDefinitions();
        if ($options['sections'] && count($options['sections'])) {
            foreach ($options['sections'] as $secName => $secFields){
                if (is_array($secFields) && count($secFields)) {
                    $form->add($secName, $options['section_type'], [
                        'label_format' => $options['label_prefix'] . $secName,
                        'translation_domain' => $options['translation_domain']
                    ]);
                    $tmp = [];
                    foreach ($secFields as $n) {
                        $tmp[$n] = $definitions[$n];
                    }
                    $this->prepareSection($form->get($secName), $locales, $tmp, $options);
                } else {
                    $this->prepareSection($form, $locales, [$secFields => $definitions[$secFields]], $options);
                }
            }
        } else {
            $this->prepareSection($form, $locales, $definitions, $options);
        }

    }

    public function prepareSection($form, $locales, $definitions, $options)
    {
        foreach ($definitions as $name => $def) {
            $opt = [
                'required' => false,
                'label_format' => $options['label_prefix'] . $name
            ];
            if ($def->locale) {
                foreach ($locales as $loc) {
                    if (count($locales) > 1) {
                        die('implement mutli loc value');
                    }
                    $form->add($name . '_' . $loc, $def->type, array_merge($opt, $def->options));
                }
            } else {
                $form->add($name, $def->type, array_merge($opt, $def->options));
            }
        }
    }

    public function getParent()
    {
        return FormType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('fields', [])
            ->addAllowedTypes('fields', 'array')
            ->setDefault('label_prefix', '')
            ->setDefault('translation_domain', 'admin')
            ->setDefault('sections', false)
            ->setDefault('inherit_data', false)
            ->setDefault('value_list', null)
            ->setDefault('section_type', FieldsetType::class);
    }
}

