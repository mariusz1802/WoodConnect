<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Form\Type\RegisterType;
use w3des\AdminBundle\Entity\PageUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class PageSecurityController extends Controller
{

    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        $registerForm = $this->createForm(RegisterType::class);

        return $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error' => $error,
            'registerForm' => $registerForm->createView(),
            'referer' => $this->getReferer(true)
        ));
    }

    /**
     * @Method("GET")
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {}

    /**
     * @Route("/register", name="register")
     */
    public function registerAction(Request $request)
    {
        $registerForm = $this->createForm(RegisterType::class, new PageUser());
        $registerForm->handleRequest($request);
        if ($registerForm->isSubmitted() && $registerForm->isValid()) {
            $user = $registerForm->getData();
            if ($registerForm->get('plainPassword')->getData()) {
                $pass = $this
                ->get('security.password_encoder')
                ->encodePassword($user, $registerForm->get('plainPassword')->getData());
                $user->setPassword($pass);
                $user->setEmail(\mb_strtolower($user->getEmail()));
                $em = $this->getDoctrine()->getManager();
                if (!trim($user->getEmail())) {
                    $registerForm->get('email')->addError(new FormError("Podaj email aby założyć konto"));
                } elseif ($em->getRepository(PageUser::class)->findOneBy(['email' => $user->getEmail()]) != null) {
                    $registerForm->get('email')->addError(new FormError("Istnieje już taki użytkownik"));
                } else {
                    $em->persist($user);
                    $em->flush();

                    //$this->sendMail($user);

                    $request->getSession()->set('_user_data', null);
                    $token = new UsernamePasswordToken($user, null, "main", ['ROLE_USER']);
                    $this->get('security.token_storage')->setToken($token);
                    $event = new InteractiveLoginEvent($request, $token);
                    $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
                    $request->getSession()->getFlashBag()->add('info', 'Użytkownik zarejestrowany, proszę pobrać plik');
                    return $this->redirect($request->request->get('_target_path'));
                }

            } else {
                $request->getSession()->set('_user_data', $user);
                //$this->sendMail($user);
                $token = new UsernamePasswordToken($user->getFirstName().' ' . $user->getLastName(), null, "main", ['ROLE_USER']);
                $this->get('security.token_storage')->setToken($token);
                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
                $request->getSession()->getFlashBag()->add('info', 'Dane zapisane, proszę pobrać plik');

                return $this->redirect($request->request->get('_target_path'));
            }
        }

        return $this->render('security/login.html.twig', array(
            'last_username' => '',
            'error' => '',
            'registerForm' => $registerForm->createView(),
            'referer' => $this->getReferer(false)
        ));
    }

    private function sendMail(PageUser $user)
    {
        if (!$this->get('settings')->get('mail_download_to')) {
            return;
        }
        $msg = \Swift_Message::newInstance($user->getId() ? 'Nowy użytkownik w systemie' : 'Pobranie bez konta', "Stworzono nowego użytkownika
Nowy użytkownik w systemie:

Imię i nazwisko: " . $user . "
Telefon: " . $user->getPhone() . "
Email: " . $user->getEmail() . "

--
Zespół optident.pl
", 'text/plain', 'utf-8');
        foreach (\explode(',', $this->get('settings')->get('mail_download_to') ?: $this->get('settings')->get('mail_download_to')) as $m) {
            $msg->addTo(trim($m));
        }
        $msg->setFrom($this->get('settings')->get('mail_from'), $this->get('settings')->get('mail_from_name'));

        $this->get('mailer')->send($msg);
    }

    /**
     * @Route("/remind", name="remind.password")
     */
    public function remindAction(Request $request)
    {
        $fb = $this->createFormBuilder();
        $fb->add('email', EmailType::class, ['label' => 'Email']);

        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            /** @var \w3des\AdminBundle\Entity\PageUser $user */
            $user = $em->getRepository(PageUser::class)->findOneBy(['email' => \strtolower($form->get('email')->getData())]);
            if (!$user || !$user->isEnabled()) {
                $request->getSession()->getFlashBag()->add('info', 'Konto niedostępne');
            } else {
                $pass = \uniqid();
                $user->setPassword($this
                    ->get('security.password_encoder')
                    ->encodePassword($user, $pass));
                $msg = \Swift_Message::newInstance('Przypomnienieni hasła w serwisie optident.pl', 'Witaj ' . $user . "
Twoje nowe hasło: " . $pass . "

--
Zespół optident.pl
", 'text/plain', 'utf-8');
                $msg->addTo($user->getEmail());
                $msg->setFrom($this->get('settings')->get('mail_from'), $this->get('settings')->get('mail_from_name'));

                $em->beginTransaction();
                $em->persist($user);
                $em->flush();
                $this->get('mailer')->send($msg);
                $em->commit();

                $request->getSession()->getFlashBag()->add('info', 'Nowe hasło zostało wysłane na podany adres e-mail');

                return $this->redirect($this->getReferer(false) ?: $this->generateUrl('homepage'));
            }
        }

        $registerForm = $this->createForm(RegisterType::class, new PageUser());

        return $this->render('security/remind.html.twig', array(
            'last_username' => '',
            'error' => '',
            'form' => $form->createView(),
            'registerForm' => $registerForm->createView(),
            'referer' => $this->getReferer(false)
        ));
    }

    protected function getReferer($useHeader)
    {
        $request = $this->get('request_stack')->getCurrentRequest();
        $ref = '';
        if ($request->request->has('_target_path')) {
            $ref = $request->request->get('_target_path');
        } elseif ($useHeader && $request->headers->has('referer')) {
            $ref = $request->headers->get('referer');
        }
        if (\strpos($ref, $request->getHost()) === false) {
            $ref = '';
        }
        if (!$ref) {
            $ref = $request->getSession()->get('referer', '');
        } else {
            $request->getSession()->set('referer', $ref);
        }

        return $ref;
    }
}

