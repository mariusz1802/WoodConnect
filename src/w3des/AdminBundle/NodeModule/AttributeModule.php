<?php
namespace w3des\AdminBundle\NodeModule;

use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use w3des\AdminBundle\Entity\NodeModule;
use w3des\AdminBundle\Form\Type\NodeModuleType;
use w3des\AdminBundle\Model\NodeModuleInterface;
use w3des\AdminBundle\Model\ValueDefinition;
use w3des\AdminBundle\Service\Nodes;

class AttributeModule implements NodeModuleInterface
{

    protected $twig;
    protected $nodes;
    protected $attrs;

    public function __construct(\Twig_Environment $templating, Nodes $nodes)
    {
        $this->twig = $templating;
        $this->nodes = $nodes;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'Atrybuty';
    }

    /**
     * {@inheritDoc}
     */
    public function getFormType()
    {
        return NodeModuleType::class;
    }

    public function getFormTypeOptions()
    {
        return [];
    }

    public function getFormFields()
    {
        if (!$this->attrs) {
            $this->attrs = [];
            foreach ($this->nodes->getNodes('attribute') as $attr) {
                $this->attrs['attr_' . $attr->getId() . '_content' ] = new ValueDefinition('attr_' . $attr->getId() . '_content', [
                    'type' => CKEditorType::class,
                    'options' => [
                        'config' => [
                            'bodyClass' => 'text text-content'
                        ],
                        'label' => $this->nodes->getVariable($attr, 'title') . ' treść'
                    ]
                ]);
            }
        }
        return $this->attrs;
    }

    /**
     * {@inheritDoc}
     */
    public function render(NodeModule $mod)
    {
        return $this->twig->render('nodes/attributes.html.twig', [
            'module' => $mod,
            'attrs' => $this->nodes->getNodes('attribute')
        ]);
    }
}

