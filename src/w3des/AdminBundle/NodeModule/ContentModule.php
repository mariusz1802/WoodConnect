<?php
namespace w3des\AdminBundle\NodeModule;

use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use w3des\AdminBundle\Entity\NodeModule;
use w3des\AdminBundle\Form\Type\ContentModuleType;
use w3des\AdminBundle\Model\NodeModuleInterface;
use w3des\AdminBundle\Model\ValueDefinition;

class ContentModule implements NodeModuleInterface
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
        return 'Treść';
    }

    /**
     * {@inheritDoc}
     */
    public function getFormType()
    {
        return ContentModuleType::class;
    }

    public function getFormTypeOptions()
    {
        return [];
    }

    public function getFormFields()
    {
        return [
            'content' => new ValueDefinition('content', [
                'label' => 'Treść',
                'type' => CKEditorType::class,
                'index' => true,
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
        return $this->twig->render('nodes/content.html.twig', [
            'module' => $mod
        ]);
    }
}

