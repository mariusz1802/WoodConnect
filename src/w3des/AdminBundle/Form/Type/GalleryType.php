<?php
namespace w3des\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use w3des\AdminBundle\Service\Nodes;
use w3des\AdminBundle\Service\Values;

class GalleryType extends AbstractType
{

    protected $nodes;

    protected $values;

    public function __construct(Nodes $nodes, Values $values)
    {
        $this->nodes = $nodes;
        $this->values = $values;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'dir' => 'images',
            'inherit_data' => false,
            'thmb' => true
        ])->setRequired('gallery_type');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('dir', $options['dir']);

        $prototype = $builder->create('__name__', GalleryItemType::class, [
            'label' => false,
            'gallery_type' => $options['gallery_type'],
            'thmb' => $options['thmb']
        ]);
        $builder->setAttribute('prototype', $prototype);

        $builder->setAttribute('gallery_type', $options['gallery_type']);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $ev) use ($options) {
            foreach ($ev->getForm() as $tmp => $d) {
                if (! isset($ev->getData()[$tmp])) {
                    $ev->getForm()
                        ->remove($tmp);
                }
            }
            if ($ev->getData()) {
                foreach ($ev->getData() as $tmp => $data) {
                    if (! isset($ev->getForm()[$tmp])) {
                        $ev->getForm()
                            ->add($tmp, GalleryItemType::class, [
                            'label' => false,
                            'gallery_type' => $ev->getForm()
                                ->getConfig()
                                ->getAttribute('gallery_type'),
                                'thmb' => $options['thmb']
                        ]);
                    }
                }
            }
        });

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $ev) use ($options) {
            if ($ev->getData()) {
                foreach ($ev->getData() as $tmp => $data) {
                    $ev->getForm()
                        ->add($tmp, GalleryItemType::class, [
                        'label' => false,
                        'gallery_type' => $ev->getForm()
                            ->getConfig()
                            ->getAttribute('gallery_type'),
                            'thmb' => $options['thmb']
                    ]);
                }
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $ev) {
            $data = $ev->getData();
            foreach ($data as $k => $v) {
                if (! isset($ev->getForm()[$k])) {
                    unset($data[$k]);
                }
            }
            $ev->setData($data);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['dir'] = $form->getConfig()->getAttribute('dir');
        $prototype = $form->getConfig()->getAttribute('prototype');
        $view->vars['prototype'] = $prototype->getForm()
            ->setParent($form)
            ->createView($view);
        $view->vars['thmb'] = $options['thmb'];
    }
}

