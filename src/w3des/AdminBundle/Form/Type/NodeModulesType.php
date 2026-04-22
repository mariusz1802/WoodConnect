<?php
namespace w3des\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use w3des\AdminBundle\Form\EventListener\NodeModulesSubscriber;
use w3des\AdminBundle\Service\Nodes;

class NodeModulesType extends AbstractType
{

    protected $nodes;

    public function __construct(Nodes $nodes)
    {
        $this->nodes = $nodes;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $mods = [];
        foreach ($options['modules'] as $m) {
            $mods[$m['type']] = $this->nodes->getModule($m['type']);

            $prototype = $builder->create('_prototype_', $mods[$m['type']]->getFormType(), array_merge([
                '_module' => $mods[$m['type']],
                'label' => $mods[$m['type']]->getLabel()
            ], $mods[$m['type']]->getFormTypeOptions()));
            $builder->setAttribute('prototype_' . $m['type'], $prototype);
        }
        $builder->setAttribute('modules', $mods);
        $builder->setAttribute('section', $options['section']);

        $builder->addEventSubscriber(new NodeModulesSubscriber($this->nodes));
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['modules'] = $form->getConfig()->getAttribute('modules');
        $view->vars['prototype'] = [];
        foreach ($options['modules'] as $m) {
            $prototype = $form->getConfig()->getAttribute('prototype_' . $m['type']);
            $view->vars['prototype'][$m['type']] = $prototype->getForm()
                ->setParent($form)
                ->createView($view);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if ($form->getConfig()->hasAttribute('prototype') && $view->vars['prototype']->vars['multipart']) {
            $view->vars['multipart'] = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('modules');
        $resolver->setRequired('section');
        $resolver->setDefault('label', false);
    }
}

