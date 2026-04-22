<?php
namespace w3des\AdminBundle\Model;

use w3des\AdminBundle\Entity\NodeModule;

interface NodeModuleInterface
{
    public function getLabel();

    public function getFormType();

    public function getFormTypeOptions();

    public function getFormFields();

    public function render(NodeModule $mod);
}

