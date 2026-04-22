<?php
namespace w3des\AdminBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

class AdminMenuEvent extends Event
{

    const ADMIN_MENU = 'admin.menu';

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var ItemInterface
     */
    private $menu;

    public function __construct(FactoryInterface $factory, ItemInterface $menu)
    {
        $this->factory = $factory;
        $this->menu = $menu;
    }

    public function getFactory()
    {
        return $this->factory;
    }

    public function getMenu()
    {
        return $this->menu;
    }
}

