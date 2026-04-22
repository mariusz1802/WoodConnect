<?php
namespace w3des\AdminBundle\NodeModule;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use w3des\AdminBundle\Entity\NodeModule;
use w3des\AdminBundle\Form\Type\NodeModuleType;
use w3des\AdminBundle\Model\NodeModuleInterface;
use w3des\AdminBundle\Model\ValueDefinition;

class MapModule implements NodeModuleInterface
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
        return 'Mapa';
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
            'content' => new ValueDefinition('content', [
                'label' => 'Treść w dymku',
                'type' => TextareaType::class
            ]),
            'lat' => new ValueDefinition('lat', [
                'label' => 'Szerokość'
            ]),
            'lng' => new ValueDefinition('lng', [
                'label' => 'Długość'
            ])
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function render(NodeModule $mod)
    {
        return $this->twig->render('nodes/map.html.twig', [
            'mod' => $mod
        ]);
    }
}

