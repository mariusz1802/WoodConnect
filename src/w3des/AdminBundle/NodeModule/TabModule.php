<?php
namespace w3des\AdminBundle\NodeModule;

use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use w3des\AdminBundle\Entity\NodeModule;
use w3des\AdminBundle\Form\Type\NodeModuleType;
use w3des\AdminBundle\Model\NodeModuleInterface;
use w3des\AdminBundle\Model\ValueDefinition;

class TabModule implements NodeModuleInterface
{

    protected $twig;

    public function __construct(\Twig_Environment $templating = null)
    {
        $this->twig = $templating;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'Zakładka';
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
            'title' => new ValueDefinition('title', [
                'label' => 'Tytuł',
                'type' => TextType::class
            ]),
            'content' => new ValueDefinition('content', [
                'label' => 'Treść',
                'type' => CKEditorType::class,
                'options' => [
                    'config' => [
                        'bodyClass' => 'text text-content'
                    ]
                ]
            ])
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function render(NodeModule $mod)
    {
        return '';
        return $this->twig->render('nodes/content.html.twig', [
            'module' => $mod
        ]);
    }
}

