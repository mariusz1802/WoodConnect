<?php
namespace w3des\AdminBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;
use w3des\AdminBundle\Entity\Setting;
use w3des\AdminBundle\Model\ValueDefinition;
use w3des\AdminBundle\Model\ValueList;

class Settings
{

    protected $em;

    protected $stack;

    protected $loaded = null;

    protected $values;

    protected $sections;

    /**
     * @var ValueDefinition[]
     */
    protected $fields = [];

    public function __construct(EntityManager $entityManager, RequestStack $stack, Values $values, $sections, $fields)
    {
        $this->em = $entityManager;
        $this->stack = $stack;
        $this->values = $values;
        $this->sections = $sections;
        foreach ($fields as $k => $v) {
            $this->fields[$k] = new ValueDefinition($k, $v);
        }
    }

    protected function load($locale)
    {
        if (isset($this->loaded[$locale])) {
            return;
        }
        $this->loaded[$locale] = new ValueList([$locale], $this->fields);

        $this->loaded[$locale]->loadModels($this->em->getRepository(Setting::class)->findBy([
            'locale' => ['',$locale]
        ]));
    }

    public function set($name, $value, $locale = null)
    {
        if ($locale === null && $this->fields[$name]->locale) {
            $locale = $this->stack->getCurrentRequest()->getLocale();
        }
        if (! $this->fields[$name]->locale) {
            $locale = '';
        }
        $def = $this->fields[$name];

        $list = new ValueList([$locale], [$def->name => $def]);
        $list->loadModels($this->em->getRepository(Setting::class)->findBy([
            'name' => $def->name
        ],  [
            'locale' => 'asc',
            'pos' => 'asc'
        ]));
        $this->values->handleValues($list, function() {
            $tmp = new Setting();
            $this->em->persist($tmp);
            return $tmp;
        }, function($var) {
            $this->em->persist($var);
        }, function($var) {
            $this->em->remove($var);
        }) ;

        $this->em->flush();
        $this->loaded = [];
    }

    public function get($name, $default = null, $locale = null)
    {
        if ($locale === null && $this->fields[$name]->locale) {
            $locale = $this->stack->getCurrentRequest()->getLocale();
        }
        $this->load($locale);
        $def = $this->fields[$name];


        return $this->loaded[$locale][$name . ($def->locale ? '_' . $locale : '') ] ?: $default;
    }

    public function getSections()
    {
        return $this->sections;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getField($name)
    {
        return $this->fields[$name];
    }
}

