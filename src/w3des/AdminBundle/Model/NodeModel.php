<?php
namespace w3des\AdminBundle\Model;

use w3des\AdminBundle\Entity\Node;
use w3des\AdminBundle\Entity\NodeModule;

class NodeModel
{

    /**
     * @var array
     */
    protected $config;

    /**
     * @var ValueList
     */
    protected $fields;

    /**
     */
    protected $modules;

    protected $node;

    public function __construct(Node $node, array $cfg, $locales = null)
    {
        $this->node = $node;
        $this->fields = new ValueList($locales == null ? [
            $node->getLocale()
        ] : $locales, $cfg['fields']);
        if ($node->getId()) {
            $this->fields->loadModels($node->getVariables());
        } else {
            $tmp = [];
            foreach ($cfg['fields'] as $def) {
                if ($def->locale) {
                    foreach ($locales as $loc) {
                        $tmp[$def->name . '_' . $loc] = $def->default;
                    }
                } else {
                    $tmp[$def->name] = $def->default;
                }
            }
            $this->fields->fromArray($tmp);
        }
        foreach ($cfg['sections'] as $name => $v) {
            if (! count($v['modules'])) {
                continue;
            }
            $this->modules[$name] = [];
            if ($node->getId()) {
                foreach ($node->getModules() as $mod) {
                    if ($mod->getSection() == $name) {
                        $this->modules[$name][] = $mod;
                    }
                }
            } else {
                $pos = 0;
                foreach ($v['modules'] as $tmp) {
                    if ($tmp['default']) {
                        $n = new NodeModule();
                        $n->setType($tmp['type']);
                        $n->setNode($this->node);
                        $n->setSection($name);
                        $n->setPos($pos);
                        $this->node->getModules()->add($n);
                        $this->modules[$name][] = $n;
                        $pos++;
                    }
                }
            }
        }
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getModules()
    {
        return $this->modules;
    }

    public function setModules($modules)
    {
        $this->modules = $modules;

        $ids = [];
        $sections = [];
        foreach ($modules as $section => $mods) {
            /** @var \w3des\AdminBundle\Entity\NodeModule $mod */
            foreach ($mods as $mod) {
                $mod->setSection($section);
                if (!$mod->getId()) {
                    $mod->setNode($this->node);
                    $this->node->getModules()->add($mod);
                } else {
                    $ids[] = $mod->getId();
                }
            }
            $sections[] = $section;
        }
        foreach ($this->node->getModules() as $mod) {
            if ($mod->getId() && \in_array($mod->getSection(), $sections) && !\in_array($mod->getId(), $ids)) {
                $this->node->getModules()->removeElement($mod);
            }
        }
        return $this;
    }

}

