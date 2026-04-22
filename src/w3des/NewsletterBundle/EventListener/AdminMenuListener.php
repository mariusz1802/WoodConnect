<?php
namespace w3des\NewsletterBundle\EventListener;

use w3des\AdminBundle\Event\AdminMenuEvent;
use Symfony\Component\Translation\TranslatorInterface;

class AdminMenuListener
{
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function onMenuConfigure(AdminMenuEvent $ev)
    {
        $newsletter = $ev->getMenu()->addChild($this->translator->trans('newsletter', [],'newsletter'), [
            'route' => 'admin.newsletter.contents',
            'extras' => [
                'ico' => 'inbox',
                'routes' => [
                    [
                        'route' => 'admin.newsletter.content.add'
                    ],
                    [
                        'route' => 'admin.newsletter.content.edit'
                    ],
                    [
                        'route' => 'admin.newsletter.subscribers'
                    ]
                ]
            ]
        ]);

        $newsletter->addChild($this->translator->trans('contents', [], 'newsletter'), [
            'route' => 'admin.newsletter.contents',
            'extras' => [
                'routes' => [
                    [
                        'route' => 'admin.newsletter.content.add'
                    ],
                    [
                        'route' => 'admin.newsletter.content.edit'
                    ]
                ]
            ]
        ]);
        $newsletter->addChild($this->translator->trans('subscribers', [], 'newsletter'), [
            'route' => 'admin.newsletter.subscribers',

        ]);
    }
}

