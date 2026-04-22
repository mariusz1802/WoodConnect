<?php
namespace w3des\AdminBundle\NodeModule;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use w3des\AdminBundle\Entity\NodeModule;
use w3des\AdminBundle\Form\Type\GalleryModuleType;
use w3des\AdminBundle\Model\NodeModuleInterface;
use w3des\AdminBundle\Model\ValueDefinition;
use w3des\AdminBundle\Service\Nodes;
use Symfony\Component\Form\FormFactoryInterface;

class FileModule implements NodeModuleInterface
{

    protected $twig;

    protected $nodes;

    protected $formFactory;

    public function __construct(\Twig_Environment $templating, Nodes $nodes, FormFactoryInterface $formFactory)
    {
        $this->twig = $templating;
        $this->nodes = $nodes;
        $this->formFactory = $formFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'Pliki';
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
            'dir' => 'files',
            'thmb' => false,
            'gallery_type' => 'file'
        ];
    }

    public function getFormFields()
    {
        return [
            'title' => new ValueDefinition('title', [
                'storeType' => 'string',
                'type' => TextType::class,
                'default' => '',
                'options' => [
                    'label_format' => 'node.field.title'
                ]
            ])
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function render(NodeModule $mod)
    {
        return $this->twig->render('nodes/files.html.twig', [
            'items' => $mod->getChildren(),
            'mod' => $mod
        ]);
    }
}

