<?php
namespace w3des\AdminBundle\Service;

use Doctrine\ORM\EntityManager;
use w3des\AdminBundle\Entity\Node;
use w3des\AdminBundle\Model\NodeModuleInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use w3des\AdminBundle\Entity\NodeModule;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Doctrine\ORM\Query\Expr\Join;
use w3des\AdminBundle\Model\ValueDefinition;
use ZendSearch\Lucene\Document;
use ZendSearch\Lucene\Document\Field;
use Doctrine\ORM\Tools\Pagination\Paginator;

class Nodes implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $em;

    protected $cfg;

    protected $vals;

    protected $router;

    protected $defaultLocale;

    protected $modules;

    public function __construct(EntityManager $em, Values $vals, Router $router, $defaultLocale, array $cfg, array $serviceModules)
    {
        $this->em = $em;
        $this->cfg = $cfg;
        foreach ($this->cfg as $name => &$c) {
            foreach ($c['fields'] as $f => &$fc) {
                $this->cfg[$name]['fields'][$f] = new ValueDefinition($f, $fc);
            }
        }
        $this->vals = $vals;
        $this->router = $router;
        $this->defaultLocale = $defaultLocale;
        $this->modules = $serviceModules;
    }

    public function getCfg()
    {
        return $this->cfg;
    }


    /**
     * @return ValueDefinition
     */
    public function getFieldDefinition($type, $name)
    {

        return $this->cfg[$type]['fields'][$name];
    }

    public function getVariable($node, $name, $locale = null)
    {
        if ($node instanceof NodeModule) {
            $cfg = $this->getModule($node->getType())
                ->getFormFields()[$name];
        } else {
            $cfg = $this->getFieldDefinition($node->getType(), $name);
        }
        return $this->vals->readValue($node->getVariables(), $cfg, '');
    }

    /**
     * @return ValueDefinition[]
     */
    public function getFields($type)
    {
        return $this->cfg[$type]['fields'];
    }

    /**
     * @return NodeModuleInterface
     */
    public function getModule($moduleName)
    {
        if (! isset($this->modules[$moduleName])) {
            $this->modules[$moduleName] = new $moduleName();
        } else if (\is_string($this->modules[$moduleName])) {
            $this->modules[$moduleName] = $this->container->get($this->modules[$moduleName]);
        }
        return $this->modules[$moduleName];
    }

    public function getSectionModules(Node $node, $section)
    {
        $sec = [];
        foreach ($node->getModules() as $m) {
            if ($m->getSection() == $section) {
                $sec[] = $m;
            }
        }

        return $sec;
    }

    public function getUrl(Node $node, $mode = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        if (! $node->getUrl()) {
            return null;
        }
        if ($node->getLocale() == '' || $node->getLocale() == $this->defaultLocale) {
            return $this->router->generate('node', [
                'path' => $node->getUrl()
                    ->getPath()
            ], $mode);
        }

        return $this->router->generate('node_locale', [
            'path' => $node->getUrl()
                ->getPath(),
            'locale' => $node->getLocale()
        ], $mode);
    }

    public function getNodes($type, $cfg = [])
    {
        $qb = $this->em->getRepository(Node::class)->createQueryBuilder('n');
        $qb->andWhere('n.type = :type')->setParameter('type', $type);
        if (isset($cfg['rootModule'])) {
            $qb->andWhere('n.rootModule = :root')->setParameter('root', $cfg['rootModule']);
        }
        if (!isset($cfg['orderBy'])) {
            $qb->addOrderBy('n.pos');
        }
        $num = 0;
        if (isset($cfg['where'])) {
            foreach ($cfg['where'] as $field => $val) {
                $qb->innerJoin('n.variables', 'v_' . $field . $num, Join::WITH, 'v_' . $field . $num . '.name = \'' . $field . '\'');
                if (is_array($val)) {
                    $qb->andWhere('v_' . $field . $num . '.intValue in (:val' . $num . ')')->setParameter('val' . $num, $val);
                } else {
                    $qb->andWhere('v_' . $field . $num. '.intValue = :val' . $num)->setParameter('val' . $num, $val);
                }
            }
            $num ++;
        }
        if (isset($cfg['orderBy'])) {
            foreach ($cfg['orderBy'] as $field => $direction) {
                $qb->innerJoin('n.variables', 'v_' . $field . $num, Join::WITH, 'v_' . $field . $num . '.name = \'' . $field . '\'');
                $tmp = $this->getFieldDefinition($type, $field);
                switch ($tmp->storeType) {
                    case 'string':
                        $qb->orderBy('v_' . $field . $num . '.stringValue', $direction);
                        break;
                    case 'integer':
                        $qb->orderBy('v_' . $field . $num . '.intValue', $direction);
                        break;
                    case 'date':
                    case 'datetime':
                        $qb->orderBy('v_' . $field . $num . '.dateTimeValue', $direction);
                        break;
                }
            }
            $num ++;
        }
        if (isset($cfg['pagging']) && $cfg['pagging']) {
            $q = new Paginator($qb->getQuery());
            $max = ceil($q->count()/$cfg['max']);
            $page = max($cfg['page'], 1);
            $page = min($page, $max);
            $q->getQuery()->setMaxResults($cfg['max'])->setFirstResult(($page - 1) * $cfg['max']);
            return [
                'list' => $q,
                'current' => $page,
                'pages' => $max
            ];
        } elseif (isset($cfg['max'])) {
            $qb->setMaxResults($cfg['max']);
        }


        return $qb->getQuery()->execute();
    }

    public function fetch($id)
    {
        return $this->em->find(Node::class, $id);
    }

    public function getDocument(Node $node)
    {
        if (!$this->cfg[$node->getType()]['index']) {
            return null;
        }
        $doc = new Document();
        $cnt = '';
        foreach ($this->getFields($node->getType()) as $field) {
            if ($field->index) {
                $cnt .= $this->getIndexValue($node, $field->name). ' ';
            }
        }
        foreach ($node->getModules() as $mod) {
            $def = $this->getModule($mod->getType());
            foreach ($def->getFormFields() as $field) {
                if ($field->index) {
                    $cnt .= $this->getIndexValue($mod, $field->name) . ' ';
                }
            }
        }
        $doc->addField(Field::text('content', $cnt));
        $doc->addField(Field::keyword('node_id', $node->getId().''));
        return $doc;
    }

    protected function getIndexValue($model, $name)
    {
        return strip_tags(html_entity_decode($this->getVariable($model, $name)));
    }

}

