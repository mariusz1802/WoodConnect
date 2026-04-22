<?php
namespace w3des\AdminBundle\Twig;

use w3des\AdminBundle\Entity\Node;
use w3des\AdminBundle\Entity\NodeModule;
use w3des\AdminBundle\Service\Nodes;
use w3des\AdminBundle\Service\Settings;

class AdminExtension extends \Twig_Extension
{

    protected $settings;

    protected $nodes;

    public function __construct(Settings $settings, Nodes $nodes)
    {
        $this->settings = $settings;
        $this->nodes = $nodes;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('sett', [
                $this,
                'getSetting'
            ]),
            new \Twig_SimpleFunction('load_nodes', [
                $this,
                'getNodes'
            ])

        ];
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('variable', [
                $this,
                'getNodeVariable'
            ]),
            new \Twig_SimpleFilter('section', [
                $this,
                'getSectionModules'
            ]),
            new \Twig_SimpleFilter('url', [
                $this,
                'getNodeUrl'
            ]),
            new \Twig_SimpleFilter('youtube_embed', [
                $this,
                'getYoutubeEmbed'
            ]),
            new \Twig_SimpleFilter('render_module', [
                $this,
                'getRenderModule'
            ], [
                'is_safe' => array(
                    'html',
                    'js',
                    'css'
                )
            ])
        ];
    }

    public function getSetting($name, $default = null, $locale = null)
    {
        return $this->settings->get($name, $default, $locale);
    }

    public function getNodeVariable($node, $name, $locale = null)
    {
        return $this->nodes->getVariable($node, $name, $locale);
    }

    public function getSectionModules(Node $node, $section)
    {
        return $this->nodes->getSectionModules($node, $section);
    }

    public function getRenderModule(NodeModule $mod)
    {
        return $this->nodes->getModule($mod->getType())
            ->render($mod);
    }

    public function getNodes($type, $cfg = [])
    {
        return $this->nodes->getNodes($type, $cfg);
    }

    public function getNodeUrl($node)
    {
        return $this->nodes->getUrl($node);
    }

    public function getYoutubeEmbed($url)
    {
        $matches = [];
        if (strpos($url, 'youtu.be') !== false) {
	        preg_match('#youtu.be/([^&]*)#i', $url, $matches);
        } else {
	        preg_match('#v=([^&]*)#i', $url, $matches);
        }
        
        return '//www.youtube.com/embed/' . $matches[1] . '?version=3&autoplay=1';
    }
}

