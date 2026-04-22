<?php
namespace w3des\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use w3des\AdminBundle\Entity\Node;
use w3des\AdminBundle\Entity\NodeVariable;
use w3des\AdminBundle\Model\ValueList;
use w3des\AdminBundle\Service\Nodes;
use w3des\AdminBundle\Service\Values;
use Symfony\Component\Form\FormView;

class GalleryItemType extends AbstractType
{

    protected $values;

    protected $nodes;

    public function __construct(Nodes $nodes, Values $values)
    {
        $this->values = $values;
        $this->nodes = $nodes;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['thmb'] = $options['thmb'];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fields = $this->nodes->getFields($options['gallery_type']);
        $builder->setAttribute('gallery_type', $options['gallery_type']);
        $builder->add('pos', HiddenType::class, [
            'attr' => [
                'pos' => 'gallery-pos'
            ]
        ]);
        $builder->add('fields', ValueListType::class, [
            'label' => false,
            'mapped' => false,
            'label_prefix' => 'node.field.',
            'data' => new ValueList([
                ''
            ], $fields)
        ]);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $ev) {
            if ($ev->getData() && $ev->getData()
                ->getId()) {
                $ev->getForm()
                    ->get('fields')->getData()
                    ->loadModels($ev->getData()
                    ->getVariables());
            }
        });
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $ev) {
            $node = $ev->getData();
            $this->values->handleValues($ev->getForm()
                ->get('fields')
                ->getData(), function () use ($node) {
                $var = new NodeVariable();
                $var->setNode($node);
                $node->getVariables()
                    ->add($var);
                return $var;
            }, function () {}, function ($var) use ($node) {
                $node->getVariables()
                    ->removeElement($var);
            });
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('thmb', true);
        $resolver->setRequired('gallery_type')->setDefault('data_class', Node::class)->setDefault('empty_data', function (FormInterface $form) {
            $node = new Node();
            $node->setType($form->getConfig()
                ->getAttribute('gallery_type'));
            $node->setLocale('');
            $node->setPos((int) $form->getName());

            return $node;
        });
    }
}

