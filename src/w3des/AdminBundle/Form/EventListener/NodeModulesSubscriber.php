<?php
namespace w3des\AdminBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use w3des\AdminBundle\Service\Nodes;
use w3des\AdminBundle\Entity\NodeModule;

class NodeModulesSubscriber implements EventSubscriberInterface
{

    protected $nodes;

    public function __construct(Nodes $nodes)
    {
        $this->nodes = $nodes;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit',
            FormEvents::SUBMIT => array('onSubmit', 50)
        ];
    }

    public function preSetData(FormEvent $event)
    {
        $pos = 0;
        /** @var \w3des\AdminBundle\Entity\NodeModule $module */
        foreach ($event->getForm()->all() as $k => $v) {
            if (\is_numeric($k)) {
                $event->getForm()->remove($k);
            }
        }
        foreach ($event->getData() as $module) {
            $def = $this->nodes->getModule($module->getType());
            $event->getForm()->add($pos . '', $def->getFormType(), array_merge([
                'label' => $def->getLabel(),
                '_module' => $def
            ], $def->getFormTypeOptions()));
            $pos ++;
        }
    }

    public function preSubmit(FormEvent $event)
    {
        $org = $event->getForm()->getData();
        foreach ($event->getForm()->all() as $k => $v) {
            if (! isset($event->getData()[$k])) {
                $event->getForm()->remove($k);
                unset($org[(int) $k]);
            }
        }

        $i = 0;
        if (\is_array($event->getData())) {
            foreach ($event->getData() as $pos => $data) {
                if (! $event->getForm()->has($pos)) {

                    $def = $this->nodes->getModule($data['type']);
                    $event->getForm()->add($pos . '', $def->getFormType(), array_merge([
                        'label' => $def->getLabel(),
                        '_module' => $def
                    ], $def->getFormTypeOptions()));
                }

                $i ++;
            }
        }

    }

    public function onSubmit(FormEvent $event)
    {
        $data = $event->getData();
        foreach ($data as $num => $mod) {
            if (!isset($event->getForm()[$num.''])) {
                unset($data[$num]);
            }
        }
        $event->setData($data);
    }
}

