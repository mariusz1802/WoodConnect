<?php
namespace w3des\AdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use w3des\AdminBundle\Util\ValueTypeDecoder;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('w3des_admin');

        $this->addSettingsSection($rootNode);
        $this->addNodeSection($rootNode);

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }


    private function addNodeSection(ArrayNodeDefinition $root)
    {
        //@formatter:off
        $root->children()
                ->arrayNode('nodes')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->booleanNode('sortable')->isRequired()->end()
                            ->booleanNode('locale')->isRequired()->end()
                            ->scalarNode('icon')->defaultValue('edit')->end()
                            ->scalarNode('url')->defaultNull()->end()
                            ->scalarNode('title')->defaultNull()->end()
                            ->booleanNode('enabled')->defaultTrue()->end()
                            ->integerNode('maxDepth')->defaultValue(1)->end()
                            ->booleanNode('index')->defaultValue(false)->end()
                            ->variableNode('redirect_empty')->defaultValue(null)->end()
                            ->arrayNode('sections')
                                ->requiresAtLeastOneElement()
                                ->useAttributeAsKey('name')
                                ->prototype('array')
                                    ->children()
                                        ->arrayNode('fields')
                                            ->useAttributeAsKey('name')
                                            ->prototype('array')
                                                ->beforeNormalization()->always(function($v) {
                                                    if (!isset($v['type'])) {
                                                        $v['type'] = TextType::class;
                                                    }
                                                    if (empty($v['storeType'])) {
                                                        $v['storeType'] = ValueTypeDecoder::decode($v['type']);
                                                    }

                                                    return $v;
                                                })->end()
                                                ->children()
                                                    ->booleanNode('grid')->defaultFalse()->end()
                                                    ->variableNode('default')->defaultValue(null)->end()
                                                    ->scalarNode('type')->defaultValue(TextType::class)->end()
                                                    ->scalarNode('storeType')->isRequired()->end()
                                                    ->booleanNode('locale')->defaultFalse()->end()
                                                    ->booleanNode('array')->defaultFalse()->end()
                                                    ->booleanNode('index')->defaultFalse()->end()
                                                    ->variableNode('options')->defaultValue([])->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                        ->arrayNode('modules')
                                            ->prototype('array')
                                                ->children()
                                                    ->scalarNode('type')->defaultValue(TextType::class)->end()
                                                    ->booleanNode('locale')->defaultFalse()->end()
                                                    ->variableNode('options')->defaultValue([])->end()
                                                    ->variableNode('defaultOptions')->defaultValue([])->end()
                                                    ->booleanNode('default')->defaultFalse()->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                ->end()
             ->end();

        //@formatter:off
    }

    private function addSettingsSection(ArrayNodeDefinition $root)
    {
        //@formatter:off
        $root->fixXmlConfig('settingGroup')
            ->children()
                ->arrayNode('settings')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->fixXmlConfig('setting')
                        ->useAttributeAsKey('name')
                        ->requiresAtLeastOneElement()
                        ->prototype('array')
                            ->beforeNormalization()->always(function($v) {
                                if (isset($v['fields']) && count($v['fields'])) {
                                    $v['type'] = FormType::class;
                                    $v['storeType'] = 'ignore';
                                } else if (!isset($v['type'])) {
                                    $v['type'] = TextType::class;
                                }
                                if ($v['type'] != FormType::class && empty($v['storeType'])) {
                                    $v['storeType'] = ValueTypeDecoder::decode($v['type']);
                                }

                                return $v;
                            })->end()
                            ->children()
                                ->scalarNode('type')->isRequired()->end()
                                ->scalarNode('storeType')->isRequired()->end()
                                ->booleanNode('locale')->defaultTrue()->end()
                                ->variableNode('default')->defaultValue(null)->end()
                                ->variableNode('options')->defaultValue([])->end()
                                ->booleanNode('array')->defaultFalse()->end()
                                ->arrayNode('fields')
                                    ->fixXmlConfig('setting')
                                    ->useAttributeAsKey('name')
                                    ->requiresAtLeastOneElement()
                                    ->prototype('array')
                                    ->beforeNormalization()->always(function($v) {
                                        if ($v == null) {
                                            $v = [];
                                        }
                                        if (empty($v['type'])) {
                                            $v['type'] = TextType::class;
                                        }

                                        if (!isset($v['storeType'])) {
                                            $v['storeType'] = ValueTypeDecoder::decode($v['type']);
                                        }

                                        return $v;
                                    })->end()
                                    ->children()
                                        ->booleanNode('locale')->defaultTrue()->end()
                                        ->scalarNode('type')->defaultValue(TextType::class)->end()
                                        ->variableNode('options')->defaultValue([])->end()
                                        ->scalarNode('storeType')->isRequired()->end()
                                        ->variableNode('default')->defaultValue(null)->end()
                                        ->booleanNode('array')->defaultFalse()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                  ->end()
              ->end()
           ->end()
        ;
           // @formatter:on
    }
}
