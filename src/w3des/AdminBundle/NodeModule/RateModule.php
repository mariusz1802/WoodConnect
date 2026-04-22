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
use w3des\AdminBundle\Entity\Rate;
use w3des\AdminBundle\Form\Type\RateType;
use w3des\AdminBundle\Entity\RateCategory;
use w3des\AdminBundle\Entity\RateValue;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class RateModule implements NodeModuleInterface, NodeModuleControllerInterface
{

    protected $twig;

    protected $factory;
    protected $settings;

    protected $forms = [];
    protected $mailer;
    protected $em;
    protected $session;

    public function __construct(\Twig_Environment $templating, FormFactory $factory, Settings $settings, \Swift_Mailer $mailer, EntityManager $em, SessionInterface $session)
    {
        $this->twig = $templating;
        $this->factory = $factory;
        $this->settings = $settings;
        $this->mailer = $mailer;
        $this->em = $em;
        $this->session = $session;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'Oceny';
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
        if (isset($_GET['confirm'])) {
            $rate = $this->em->find(Rate::class, $_GET['confirm']);
            if ($rate && !$rate->getConfirmed()) {
                $rate->setConfirmed(true);


                $this->session->getFlashBag()->add('info' . $mod->getId(), 'Komentarz został potwierdzony, oczekuje na akceptację moderatora.');
                $form = $this->getForm($mod, $rate);
                $body = '<p>Dane formularza:</p>';
                foreach ($form as $field) {
                    $body .= '<p><strong>' . $field->getConfig()->getOption('label') . '</strong>: ' . $field->getData() . '</p>';
                }

                $msg = \Swift_Message::newInstance('Oceń nas - nowy wpis', $body, 'text/html', 'UTF-8');
                $msg->addFrom($this->settings->get('mail_from'));
                foreach (\explode(',', $this->settings->get('mail_to')) as $m) {
                    $msg->addTo(trim($m));
                }
                $this->mailer->send($msg);
                $this->em->flush($rate);

            }
        }
        $query = $this->em->createQuery('select r from w3des\AdminBundle\Entity\Rate r where r.confirmed = true and r.approved = true order by r.createdAt desc');
        $onPage = 10;
        $q = new Paginator($query);
        $max = ceil($q->count()/$onPage);
        $page = max((int)($_GET['page'] ?? 1), 1);
        $page = min($page, $max);
        $page = max($page, 1);

        $query->setMaxResults($onPage)->setFirstResult(($page - 1) * $onPage);
        return $this->twig->render('nodes/rate.html.twig', [
            'module' => $mod,
            'form' => $this->getForm($mod)->createView(),
            'items' => $query->execute(),
            'pages' => $max,
            'page' => $page
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
            $data->setIp($request->getClientIp());
            $data->setCreatedAt(new \DateTime());
            $body = '<p>Dane formularza:</p>';
            foreach ($form as $field) {
                $body .= '<p><strong>' . $field->getConfig()->getOption('label') . '</strong>: ' . $field->getData() . '</p>';
            }
            $url = $request->getUri() . '?confirm=' . $data->getId();
            $body .= sprintf('<p></p><p>Aby potwierdzić kliknij <a href="%s">%s</a></p>', $url, $url);

            $msg = \Swift_Message::newInstance('Oceń nas - nowy wpis', $body, 'text/html', 'UTF-8');
            $msg->addFrom($this->settings->get('mail_from'));
            //foreach (\explode(',', $this->settings->get('mail_records_to') ?: $this->settings->get('mail_to')) as $m) {
            //    $msg->addTo(trim($m));
            //}
            $msg->addTo($data->getEmail());
            $rec = $form->getData();
            $this->em->persist($rec);
            $this->em->flush();

            $this->mailer->send($msg);

            $request->getSession()->getFlashBag()->add('info' . $module->getId(), 'Formularz został wysłany. Prosimy o potwierdzenie oceny za pomocą URL które wysłaliśmy na podany adres e-mail.');


            return new RedirectResponse($request->getRequestUri() . '#rate-form' . $module->getId());
        }
    }

    /**
     * @param NodeModule $module
     * @return FormInterface
     */
    protected function getForm(NodeModule $module, $rec= null)
    {
        if (!isset($this->forms[$module->getId()])) {
            if (!$rec) {
                $rec = new Rate();
                foreach ($this->em->getRepository(RateCategory::class)->findBy([], ['position' => 'asc', 'name' => 'asc']) as $cat) {
                    $v = new RateValue();
                    $v->setCategory($cat);
                    $v->setRate($rec);
                    $v->setValue(5);
                    $rec->getValues()[$cat->getId()] = $v;
                }
            }

            $this->forms[$module->getId()] = $this->factory->create(RateType::class, $rec);
        }

        return $this->forms[$module->getId()];
    }
}

