<?php
namespace w3des\AdminBundle\NodeModule;

use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use w3des\AdminBundle\Entity\NodeModule;
use w3des\AdminBundle\Form\Type\ContactType;
use w3des\AdminBundle\Form\Type\NodeModuleType;
use w3des\AdminBundle\Model\NodeModuleControllerInterface;
use w3des\AdminBundle\Model\NodeModuleInterface;
use w3des\AdminBundle\Service\Settings;
use Doctrine\ORM\EntityManager;
use w3des\AdminBundle\Form\Type\RecordsType;
use w3des\AdminBundle\Entity\Records;
use w3des\AdminBundle\Model\ValueDefinition;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use w3des\AdminBundle\Entity\Node;
use w3des\AdminBundle\Service\Nodes;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use w3des\AdminBundle\Form\Type\RecordsCadType;

class RecordsModule implements NodeModuleInterface, NodeModuleControllerInterface
{

    protected $twig;

    protected $factory;

    protected $settings;

    protected $forms = [];

    protected $mailer;

    protected $em;
    protected $node;

    public function __construct(\Twig_Environment $templating, FormFactory $factory, Settings $settings, \Swift_Mailer $mailer, EntityManager $em, Nodes $node)
    {
        $this->twig = $templating;
        $this->factory = $factory;
        $this->settings = $settings;
        $this->mailer = $mailer;
        $this->em = $em;
        $this->node = $node;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'Formularz szkoleń';
    }

    /**
     * {@inheritDoc}
     */
    public function getFormType()
    {
        return NodeModuleType::class;
    }

    public function getFormTypeOptions()
    {
        return [];
    }

    public function getFormFields()
    {
        return [
            'title' => new ValueDefinition('title', [
                'label' => 'Tytuł formularza',
                'type' => TextType::class,
                'index' => false
            ]),
            'subject' => new ValueDefinition('subject', [
                'label' => 'Tytuł maila ',
                'type' => TextType::class,
                'index' => false
            ]),
            'kind' => new ValueDefinition('kind', [
                'label' => 'Rodzaj formularza',
                'type' => ChoiceType::class,
                'storeType' => 'string',
                'index' => false,
                'options' => [
                    'choices' => [
                        'Szkolenie (domyślne)' => 'record',
                        'Kongres CAD/CAM' => 'cad_cam'
                    ]
                ]
            ])
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function render(NodeModule $mod)
    {
        return $this->twig->render('nodes/records.html.twig', [
            'module' => $mod,
            'form' => $this->getForm($mod)
                ->createView()
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function control(NodeModule $module, Request $request)
    {
        $form = $this->getForm($module);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $body = '<p>Dane formularza:</p>';
            foreach ($form as $field) {
                $body .= '<p><strong>' . $field->getConfig()->getOption('label') . '</strong>: ' . $field->getData() . '</p>';
            }
            $subject = $this->node->getVariable($module, 'subject');
            if (!$subject) {
                $subject = 'Formularz szkoleniowy';
            }

            $msg = \Swift_Message::newInstance($subject . ' - nowy wpis', $body, 'text/html', 'UTF-8');
            $msg->addFrom($this->settings->get('mail_from'));
            foreach (\explode(',', $this->settings->get('mail_records_to') ?: $this->settings->get('mail_to')) as $m) {
                $msg->addTo(trim($m));
            }
            $rec = $form->getData();
            $this->em->persist($rec);
            $this->em->flush();

            $this->mailer->send($msg);

            $msg = \Swift_Message::newInstance($subject . ' - potwierdzenie', $this->settings->get('mail_records_confirm'), 'text/html', 'UTF-8');
            $msg->addFrom($this->settings->get('mail_from'));
            $msg->addTo($rec->getEmail());
            $this->mailer->send($msg);

            $request->getSession()
                ->getFlashBag()
                ->add('info' . $module->getId(), 'Formularz został wysłany');

            return new RedirectResponse($request->getRequestUri() . '#contact' . $module->getId());
        }
    }

    /**
     * @param NodeModule $module
     * @return FormInterface
     */
    protected function getForm(NodeModule $module)
    {
        if (! isset($this->forms[$module->getId()])) {
            $kind = $this->node->getVariable($module, 'kind');
            $rec = new Records();
            if ($kind == 'cad_cam') {
                $rec->setKind('cad_cam');
                $this->forms[$module->getId()] = $this->factory->create(RecordsCadType::class, $rec, [
                    'validation_groups' => ['default', 'cad_cam']
                ]);
            } else {
                $rec->setKind('training');
                $this->forms[$module->getId()] = $this->factory->create(RecordsType::class, $rec, [
                    'validation_groups' => ['default', 'training']
                ]);
            }
        }

        return $this->forms[$module->getId()];
    }
}

