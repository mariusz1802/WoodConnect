<?php
namespace w3des\AdminBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LocaleListener implements EventSubscriberInterface
{

    protected $session;

    protected $adminLocale;

    protected $defaultPageLocale;

    protected $availableLocales;

    public function __construct(SessionInterface $session, $adminLocale, $defaultPageLocale, $availableLocales)
    {
        $this->adminLocale = $adminLocale;
        $this->defaultPageLocale = $defaultPageLocale;
        $this->availableLocales = $availableLocales;
        $this->session = $session;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => array(
                array(
                    'onKernelRequest',
                    15
                )
            )
        ];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (! $event->isMasterRequest()) {
            return;
        }
        $request = $event->getRequest();
        $path = $request->getPathInfo();
        if (! \preg_match('#^/admin#i', $path)) {
            return;
        }
        $request->setLocale($this->adminLocale);
        $loc = $this->session->get('_page_locale', $this->defaultPageLocale);
        if (!\in_array($loc, $this->availableLocales)) {
            $loc = $this->defaultPageLocale;
        }
        $request->attributes->set('_page_locale', $loc);
        $request->attributes->set('pageLocale', $loc);
        $request->setLocale($loc);
    }
}

