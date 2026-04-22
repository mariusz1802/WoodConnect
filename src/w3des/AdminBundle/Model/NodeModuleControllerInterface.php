<?php
namespace w3des\AdminBundle\Model;

use w3des\AdminBundle\Entity\NodeModule;
use Symfony\Component\HttpFoundation\Request;

interface NodeModuleControllerInterface extends NodeModuleInterface
{

    public function control(NodeModule $module, Request $request);
}

