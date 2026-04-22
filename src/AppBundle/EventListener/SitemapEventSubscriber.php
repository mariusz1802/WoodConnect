<?php
namespace AppBundle\EventListener;

use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Knp\Menu\Provider\MenuProviderInterface;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Knp\Menu\MenuItem;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use w3des\AdminBundle\Service\Nodes;

class SitemapEventSubscriber implements EventSubscriberInterface
{

    private $provider;

    private $stack;
    private $nodes;

    public function __construct(RequestStack $stack, MenuProviderInterface $provider, Nodes $nodes)
    {
        $this->provider = $provider;
        $this->stack = $stack;
        $this->nodes = $nodes;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SitemapPopulateEvent::ON_SITEMAP_POPULATE => 'populate'
        ];
    }

    /**
     * @param SitemapPopulateEvent $event
     */
    public function populate(SitemapPopulateEvent $event): void
    {
        $prefix = '';
        if ($this->stack->getCurrentRequest()) {
            $prefix = $this->stack->getCurrentRequest()->getSchemeAndHttpHost();
        }
        $this->registerBlogPostsUrls($prefix,$this->provider->get('app.menu'), $event->getUrlContainer());
        $this->registerNodes('news', $event->getUrlContainer());
        $this->registerNodes('product', $event->getUrlContainer());
    }

    public function registerBlogPostsUrls($prefix,MenuItem $menu, UrlContainerInterface $urls)
    {
        foreach ($menu as $item) {
            $urls->addUrl(new UrlConcrete($item->getUri()), 'menu');
            $this->registerBlogPostsUrls($prefix,$item, $urls);
        }
    }

    public function registerNodes($type, UrlContainerInterface $urls)
    {

        foreach ($this->nodes->getNodes($type, ['where' => ['public' => true]]) as $item) {
            $urls->addUrl(new UrlConcrete($this->nodes->getUrl($item, UrlGeneratorInterface::ABSOLUTE_URL)), $type);
        }
    }
}

