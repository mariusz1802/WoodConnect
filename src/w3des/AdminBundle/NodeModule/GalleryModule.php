<?php
namespace w3des\AdminBundle\NodeModule;

use w3des\AdminBundle\Entity\NodeModule;
use w3des\AdminBundle\Form\Type\GalleryModuleType;
use w3des\AdminBundle\Model\NodeModuleInterface;
use w3des\AdminBundle\Service\Nodes;
use w3des\AdminBundle\Model\ValueDefinition;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class GalleryModule implements NodeModuleInterface
{
    const TYPE_CLASSIC = 1;
    const TYPE_DESIGN = 2;
    const TYPE_DEKOR = 3;
    protected $twig;
    protected $nodes;

    public function __construct(\Twig_Environment $templating, Nodes $nodes)
    {
        $this->twig = $templating;
        $this->nodes = $nodes;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'Galeria';
    }

    /**
     * {@inheritDoc}
     */
    public function getFormType()
    {
        return GalleryModuleType::class;
    }

    public function getFormTypeOptions()
    {
        return [
            'dir' => 'gallery',
            'gallery_type' => 'gallery_item'
        ];
    }

    public function getFormFields()
    {
        return [
            'type' => new ValueDefinition('type', [
                'storeType' => 'integer',
                'type' => ChoiceType::class,
                'default' => 1,
                'options' => [
                    'empty_data' => 1,
                    'label_format' => 'node.field.galleryType',
                    'choices' => [
                        'Klasyczna' => self::TYPE_CLASSIC,
                        'Dekor' => self::TYPE_DEKOR,
//                         'Designerska' => self::TYPE_DESIGN
                    ]
                ]
            ]),
            'title' => new ValueDefinition('title', [
                'storeType' => 'string',
                'type' => TextType::class,
                'default' => '',
                'options' => [
                    'label_format' => 'node.field.title'
                ]
            ]),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function render(NodeModule $mod)
    {
        $type = $this->nodes->getVariable($mod, 'type', self::TYPE_CLASSIC);
        $items = $mod->getChildren()->toArray();
        if ($type == self::TYPE_DESIGN) {
            $galls = ['h' => [],'v' => [], 's' => []];
            foreach ($items  as $item) {
                $ph = $this->nodes->getVariable($item, 'photo');
                if ($ph['width'] == $ph['height']) {
                    $galls['s'][] = $item;
                } else if ($ph['width'] > $ph['height']) {
                    $galls['h'][] = $item;
                } else {
                    $galls['v'][] = $item;
                }
            }
            $galls['h'] = \array_chunk($galls['h'], 2);
            foreach ($galls['h'] as &$l) {
                if (count($l) == 1) {
                    if (isset($galls['s'][0])) {
                        $l[] = $galls['s'][0];
                        unset($galls['s'][0]);
                    }
                    if (isset($galls['s'][1])) {
                        $l[] = $galls['s'][1];
                        unset($galls['s'][1]);
                    }
                }
            }
            if (count($galls['v']) < count($galls['h']) / 2) {
                foreach (\array_chunk($galls['s'], 2) as $chunk) {
                    $galls['v'][] = $chunk;
                }
            } else {
                foreach (\array_chunk($galls['s'], 2) as $chunk) {
                    $galls['h'][] = $chunk;
                }
            }
            $start = 'h';
            $end = 'v';
            if (count($galls['v']) > count($galls['h'])) {
                $start = 'v';
                $end = 'h';
                $ratio = count($galls['h']) / count($galls['v']);
            } else {
                $ratio = count($galls['v']) / count($galls['h']);
            }
            $pos = null;
            $counter = 0;
            $items = [];
            while ($counter < count($galls[$start]) || $counter < count($galls[$end])) {
                $newPos = floor($counter * $ratio);
                if (isset($galls[$start][$counter])) {
                    $items[] = ['type' => $start, 'items' => $galls[$start][$counter]];
                }
                if ($pos === null || $newPos != $pos) {
                    $pos = $newPos;
                    if (isset($galls[$end][$pos])) {
                        $items[] = ['type' => $end, 'items' => $galls[$end][$pos]];
                    }
                }
                $counter++;
            }
            return $this->twig->render('nodes/gallery_design.html.twig', [
                'items' => $items,
                'mod' => $mod,
                'type' => $type
            ]);
        }

        return $this->twig->render('nodes/gallery.html.twig', [
            'items' => $items,
            'mod' => $mod,
                'type' => $type
        ]);
    }
}

