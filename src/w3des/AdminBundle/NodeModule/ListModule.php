<?php
namespace w3des\AdminBundle\NodeModule;

use w3des\AdminBundle\Entity\NodeModule;
use w3des\AdminBundle\Form\Type\NodeModuleType;
use w3des\AdminBundle\Model\NodeModuleInterface;

class ListModule implements NodeModuleInterface
{

    protected $twig;

    public function __construct(\Twig_Environment $templating)
    {
        $this->twig = $templating;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'Aktualności';
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
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function render(NodeModule $mod)
    {
        return $this->twig->render('nodes/news.html.twig', [
            'module' => $mod
        ]);
    }
}

