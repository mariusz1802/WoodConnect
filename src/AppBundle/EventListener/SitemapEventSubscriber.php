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
        $this->registerMenuUrls($prefix, $this->provider->get('app.menu'), $event->getUrlContainer());
        $this->registerNodes('news', $event->getUrlContainer(), 0.6);
        $this->registerNodes('product', $event->getUrlContainer(), 0.8);
        $this->registerNodes('offer', $event->getUrlContainer(), 0.8);
        $this->registerNodes('page', $event->getUrlContainer(), 0.7);
    }

    public function registerMenuUrls($prefix, MenuItem $menu, UrlContainerInterface $urls)
    {
        foreach ($menu as $item) {
            if ($item->getUri()) {
                $urls->addUrl(
                    new UrlConcrete($item->getUri(), new \DateTime(), UrlConcrete::CHANGEFREQ_WEEKLY, 0.9),
                    'menu'
                );
            }
            $this->registerMenuUrls($prefix, $item, $urls);
        }
    }

    public function registerNodes($type, UrlContainerInterface $urls, float $priority = 0.7)
    {
        foreach ($this->nodes->getNodes($type, ['where' => ['public' => true]]) as $item) {
            $url = $this->nodes->getUrl($item, UrlGeneratorInterface::ABSOLUTE_URL);
            if ($url) {
                $urls->addUrl(
                    new UrlConcrete($url, new \DateTime(), UrlConcrete::CHANGEFREQ_MONTHLY, $priority),
                    $type
                );
            }
        }
    }
}

