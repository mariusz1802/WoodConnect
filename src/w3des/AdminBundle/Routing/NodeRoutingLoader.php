<?php
namespace w3des\AdminBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

class NodeRoutingLoader extends Loader
{

    private $loaded = false;

    protected $locales;

    protected $default;

    public function __construct($locales, $default)
    {
        $this->locales = $locales;
        $this->default = $default;
    }

    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }

        $routes = new RouteCollection();

        // prepare a new route
        $path = '/{path}';
        $defaults = array(
            '_controller' => 'AppBundle:Default:node'
        );
        $route = new Route($path, $defaults, []);

        // add the new route to the route collection
        $routeName = 'node';
        $routes->add($routeName, $route);

        if (count($this->locales) > 1) {
            $path = '/_locale/{path}';
            $defaults = array(
                '_controller' => 'AppBundle:Default:node'
            );
            $route = new Route($path, $defaults, [
                '_locale' =>  \implode('|', $this->locales)
            ]);

            // add the new route to the route collection
            $routeName = 'node_locale';
            $routes->add($routeName, $route);
        }

        $this->loaded = true;

        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return 'node' === $type;
    }
}

