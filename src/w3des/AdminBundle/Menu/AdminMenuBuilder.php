<?php
namespace w3des\AdminBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;
use w3des\AdminBundle\Service\Nodes;
use w3des\AdminBundle\Service\Settings;
use w3des\AdminBundle\Event\AdminMenuEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AdminMenuBuilder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $factory;

    protected $settings;

    protected $nodes;

    protected $stack;

    protected $tranlator;

    protected $eventDispatcher;

    public function __construct(FactoryInterface $factory, Settings $sett, Nodes $nodes, RequestStack $stack, TranslatorInterface $tranlator, EventDispatcherInterface $dispatcher)
    {
        $this->factory = $factory;
        $this->settings = $sett;
        $this->nodes = $nodes;
        $this->stack = $stack;
        $this->tranlator = $tranlator;
        $this->eventDispatcher = $dispatcher;
    }

    public function createMenu(array $options)
    {
        $menu = $this->factory->createItem('root')->setExtra('translation_domain', 'admin');

        $menu->addChild($this->trans('dashboard'), array(
            'route' => 'admin.home'
        ))
            ->setExtra('ico', 'home_house');

        $menu->addChild($this->trans('users'), array(
            'route' => 'admin.users'
        ))
            ->setExtra('ico', 'user')
            ->setExtra('routes', [
            [
                'route' => 'admin.users.add'
            ],
            [
                'route' => 'admin.users'
            ],
            [
                'route' => 'admin.users.edit'
            ]
        ]);

        foreach ($this->nodes->getCfg() as $name => $cfg) {
            $routes = [];
            if (! $cfg['enabled']) {
                continue;
            }
            $sett = $menu->addChild($this->trans('node.' . $name), [
                'route' => 'admin.node',
                'routeParameters' => [
                    'type' => $name
                ],
                'extras' => [
                    'ico' => $cfg['icon']
                ]
            ]);

            $request = $this->stack->getMasterRequest();
            if ($request && \strpos($request->attributes->get('_route'), 'admin.node') === 0 && $request->attributes->get('type') == $name) {
                $sett->setCurrent(true);
            }
        }

        $this->eventDispatcher->dispatch(AdminMenuEvent::ADMIN_MENU, new AdminMenuEvent($this->factory, $menu));

        $sett = null;
        $routes = [];
        foreach (array_keys($this->settings->getSections()) as $name) {
            if (! $sett) {
                $sett = $menu->addChild($this->trans('settings'), [
                    'route' => 'admin.settings',
                    'routeParameters' => [
                        'group' => $name
                    ]
                ])
                    ->setExtras([
                    'ico' => 'gear'
                ]);
            }
            $sett->addChild($this->trans('settings.' . $name), [
                'route' => 'admin.settings',
                'routeParameters' => [
                    'group' => $name
                ]
            ])
                ->setExtras([
                'translation_domain' => 'admin'
            ]);
            $routes[] = [
                'route' => 'admin.settings',
                'routeParameters' => [
                    'group' => $name
                ]
            ];
        }
        $sett->setExtra('routes', $routes);
		/*
        $menu->addChild("Użytkownicy strony", array(
            'route' => 'admin.page_users'
        ))
            ->setExtra('ico', 'burn')
            ->setExtra('routes', [
            [
                'route' => 'admin.page_users.add'
            ],
            [
                'route' => 'admin.page_users'
            ],
            [
                'route' => 'admin.page_users.edit'
            ]
        ]);
		
        $menu->addChild("Historia pobrań", array(
            'route' => 'admin.downloads'
        ))
            ->setExtra('ico', 'time')
            ->setExtra('routes', [
            [
                'route' => 'admin.downloads.add'
            ],
            [
                'route' => 'admin.downloads'
            ],
            [
                'route' => 'admin.downloads.edit'
            ]
        ]);

        $fav = $menu->addChild("Oceny", array(
            'route' => 'admin.rate'
        ))
            ->setExtra('ico', 'favorite')
            ->setExtra('routes', [
            [
                'route' => 'admin.rate'
            ],
            [
                'route' => 'admin.rate.edit'
            ],
            [
                'route' => 'admin.rate.category'
            ],
            [
                'route' => 'admin.rate.category.add'
            ],
            [
                'route' => 'admin.rate.category.edit'
            ]
        ]);
        $fav->addChild('Lista ocen', [
            'route' => 'admin.rate'
        ])->setExtra('routes', [
            [
                'route' => 'admin.rate'
            ],
            [
                'route' => 'admin.rate.edit'
            ]
        ]);
        $fav->addChild('Kategorie', [
            'route' => 'admin.rate.category'
        ])->setExtra('routes', [
            [
                'route' => 'admin.rate.category'
            ],
            [
                'route' => 'admin.rate.category.add'
            ],
            [
                'route' => 'admin.rate.category.edit'
            ]
        ]);*/

        return $menu;
    }

    protected function trans($label)
    {
        return $this->tranlator->trans($label, [], 'admin');
    }
}

