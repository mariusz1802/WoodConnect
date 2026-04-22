<?php
namespace AppBundle\Menu;

use Doctrine\ORM\EntityManager;
use Knp\Menu\FactoryInterface;
use Knp\Menu\MenuItem;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use w3des\AdminBundle\Service\Nodes;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MenuBuilder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $stack;

    protected $em;
    
    protected $menu;

    public function __construct(FactoryInterface $factory, Nodes $nodes, RequestStack $stack, EntityManager $em)
    {
        $this->factory = $factory;
        $this->nodes = $nodes;
        $this->stack = $stack;
        $this->em = $em;
        $this->nodes = $nodes;
    }

    public function createMenu(array $options)
    {
	    if($this->menu) {
		    return $this->menu;
	    }
        $this->menu = $menu = $this->factory->createItem('root', [
	        'label' => 'Strona główna'
        ])->setExtra('translation_domain', 'messages');
        $this->add($menu, $this->em->createQuery('select n from w3desAdminBundle:Node n where n.type = :type and n.locale = :locale and n.parent is null order by n.pos')->execute([
            'type' => 'pages',
            'locale' => $this->stack->getMasterRequest()->getLocale()
        ]));

        return $menu;
    }

    protected function add(MenuItem $menu, $list)
    {
        foreach ($list as $item) {
            $url = $this->nodes->getUrl($item);
            $ch = $menu->addChild($item->getId() . '', [
                'label' => $this->nodes->getVariable($item, 'title'),
                'uri' => $this->nodes->getUrl($item, UrlGeneratorInterface::ABSOLUTE_URL)
            ]);
            $ch->setExtra('node', $item);
            if ($this->stack->getMasterRequest()->getRequestUri() == $url) {
                $ch->setCurrent(true);
            }
            $this->add($ch, $item->getChildren());
        }
    }
}

