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

class ContactModule implements NodeModuleInterface, NodeModuleControllerInterface
{

    protected $twig;

    protected $factory;
    protected $settings;

    protected $forms = [];
    protected $mailer;

    public function __construct(\Twig_Environment $templating, FormFactory $factory, Settings $settings, \Swift_Mailer $mailer)
    {
        $this->twig = $templating;
        $this->factory = $factory;
        $this->settings = $settings;
        $this->mailer = $mailer;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'Formularz kontaktowy';
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
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function render(NodeModule $mod)
    {
        return $this->twig->render('nodes/contact.html.twig', [
            'module' => $mod,
            'form' => $this->getForm($mod)->createView()
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
            $msg = \Swift_Message::newInstance('Formularz kontaktowy: ' . $data['subject'], $body, 'text/html', 'UTF-8');
            $msg->addFrom($this->settings->get('mail_from'));
            foreach (\explode(',', $this->settings->get('mail_to')) as $m) {
                $msg->addTo(trim($m));
            }
            $this->mailer->send($msg);
            $request->getSession()->getFlashBag()->add('info' . $module->getId(), 'Formularz został wysłany');

            return new RedirectResponse($request->getRequestUri() . '#contact' . $module->getId());
        }
    }

    /**
     * @param NodeModule $module
     * @return FormInterface
     */
    protected function getForm(NodeModule $module)
    {
        if (!isset($this->forms[$module->getId()])) {
            $this->forms[$module->getId()] = $this->factory->create(ContactType::class, [
                '_module' => $module->getId()
            ]);
        }

        return $this->forms[$module->getId()];
    }
}

