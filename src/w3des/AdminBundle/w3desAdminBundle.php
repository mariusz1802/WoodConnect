<?php

namespace w3des\AdminBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use w3des\AdminBundle\DependencyInjection\NodeModulePass;

class w3desAdminBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new NodeModulePass());
    }
}
