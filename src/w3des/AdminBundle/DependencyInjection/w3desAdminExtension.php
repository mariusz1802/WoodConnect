<?php

namespace w3des\AdminBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use w3des\AdminBundle\Model\ValueDefinition;
use w3des\AdminBundle\Util\ValueTypeDecoder;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class w3desAdminExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $settingsSections = [];
        $settingsFields = [];
        foreach ($config['settings'] as $name => $gr) {
            $settingsSections[$name] = [];
            foreach ($gr as $k => $v) {
                if ($v['type'] == FormType::class) {
                    $list = [];
                    foreach ($v['fields'] as $sk => $sv) {
                        $list[] = $sk;
                        $settingsFields[$sk] = $sv;
                    }
                    $settingsSections[$name][$k] = $list;
                } else {
                    $settingsSections[$name][] = $k;
                    $settingsFields[$k] = $v;
                }
            }
        }



        $container->getDefinition('settings')->addArgument($settingsSections)->addArgument($settingsFields);
        $nodes = [];
        foreach ($config['nodes'] as $nodeName => $nodeCfg) {
            $cfg = $nodeCfg;
            unset($cfg['nodes']);
            $cfg['sections'] = [];
            $cfg['fields'] = [];
            $cfg['grid'] = [];
            foreach ($nodeCfg['sections'] as $sectionName => $sectionCfg) {
                $cfg['sections'][$sectionName] = [
                    'fields' => [],
                    'modules' => []
                ];
                foreach ($sectionCfg['modules'] as $module) {
                    $cfg['sections'][$sectionName]['modules'][] = $module;
                }
                foreach ($sectionCfg['fields'] as $name => $field) {
                    if ($field['grid']) {
                        $cfg['grid'][] = $name;
                    }
                    unset($field['grid']);
                    $cfg['sections'][$sectionName]['fields'][] = $name;
                    $cfg['fields'][$name] = $field;
                }
            }

            $nodes[$nodeName] = $cfg;

        }
        $container->getDefinition('nodes')->addArgument($nodes);
    }

}
