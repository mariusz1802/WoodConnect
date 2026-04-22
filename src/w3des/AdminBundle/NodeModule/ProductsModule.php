<?php
namespace w3des\AdminBundle\NodeModule;

use w3des\AdminBundle\Entity\NodeModule;
use w3des\AdminBundle\Form\Type\NodeModuleType;
use w3des\AdminBundle\Model\NodeModuleInterface;
use w3des\AdminBundle\Service\Nodes;
use w3des\AdminBundle\Model\ValueDefinition;

class ProductsModule implements NodeModuleInterface
{

    protected $twig;
    protected $nodes;

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
        return 'Lista produktów';
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
        return [
            'category' => new ValueDefinition('category',[
                'label' => 'Kategoria',
                'type' => 'w3des\AdminBundle\Form\Type\NodeType',
                'options' => [
                    'type' => 'category'
                ]
            ])
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function render(NodeModule $mod)
    {
        $cat = $this->nodes->fetch($this->nodes->getVariable($mod, 'category'));
        return $this->twig->render('nodes/products.html.twig', [
            'module' => $mod,
            'category' => $cat
        ]);
    }
}

