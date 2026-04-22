<?php
namespace w3des\AdminBundle\NodeModule;

use w3des\AdminBundle\Entity\NodeModule;
use w3des\AdminBundle\Form\Type\GalleryModuleType;
use w3des\AdminBundle\Model\NodeModuleInterface;
use w3des\AdminBundle\Service\Nodes;

class SliderModule implements NodeModuleInterface
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
        return 'Slider';
    }

    /**
     * {@inheritDoc}
     */
    public function getFormType()
    {
        return GalleryModuleType::class;
    }

    public function getFormTypeOptions()
    {
        return [
            'dir' => 'slider'
        ];
    }

    public function getFormFields()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function render(NodeModule $mod)
    {
        return $this->twig->render('slider.html.twig', [
            'items' => $this->nodes->getNodes('image', ['rootModule' => $mod]),
            'module' => $mod
        ]);
    }
}

