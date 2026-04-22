<?php
namespace w3des\AdminBundle\Form\Type;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use w3des\AdminBundle\Entity\NodeModule;
use w3des\AdminBundle\Service\Values;
use w3des\AdminBundle\Service\Nodes;
use Symfony\Component\Form\CallbackTransformer;
use w3des\AdminBundle\Model\ValueList;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use w3des\AdminBundle\Entity\NodeModuleVariable;

class NodeModuleType extends AbstractType
{

    protected $value;

    protected $nodes;

    public function __construct(Values $value, Nodes $nodes)
    {
        $this->value = $value;
        $this->nodes = $nodes;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('_module');
        $resolver->setDefaults([
            'data_class' => NodeModule::class,
            'inherit_data' => false,
            'empty_data' => function(FormInterface $form) {
                $data = new NodeModule();

                return $data;
            }
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (count($options['_module']->getFormFields())) {
            $builder->add('fields', ValueListType::class, [
                'label' => false,
                'mapped' => false,
                'label_prefix' => 'node.field.',
                'data' => new ValueList([], $options['_module']->getFormFields())
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
                $this->value->handleValues($ev->getForm()
                    ->get('fields')
                    ->getData(), function () use ($node) {
                    $var = new NodeModuleVariable();
                    $var->setModule($node);
                    $node->getVariables()
                        ->add($var);
                    return $var;
                }, function () {}, function ($var) use ($node) {
                    $node->getVariables()
                        ->removeElement($var);
                });
            });
        }
        $builder->add('type', HiddenType::class, array(
            'data' => ClassUtils::getClass($options['_module'])
        ));
        $builder->add('pos', HiddenType::class, [
            'attr' => [
                'pos' => 'module-pos'
            ]
        ]);

        $builder->setAttribute('module', $options['_module']);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $ev) {
            foreach ($ev->getData()->getChildren() as $item) {
                $item->setRootModule($ev->getData());
            }
        });
    }

    public function getParent()
    {
        return FieldsetType::class;
    }
}

