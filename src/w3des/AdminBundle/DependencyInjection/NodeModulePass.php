<?php
namespace w3des\AdminBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class NodeModulePass implements CompilerPassInterface
{

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $registry = [];
        foreach ($container->findTaggedServiceIds('node.module') as $id => $tags) {
            $registry[$container->findDefinition($id)->getClass()] = $id;
        }
        $container->findDefinition('nodes')->addArgument($registry);
    }
}

