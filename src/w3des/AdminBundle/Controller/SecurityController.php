<?php
namespace w3des\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SecurityController extends Controller
{

    /**
     * @Route("/login", name="admin.login")
     */
    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('w3desAdminBundle:Security:login.html.twig', array(
            'last_username' => $lastUsername,
            'error' => $error
        ));
    }

    /**
     * @Method("GET")
     * @Route("/logout", name="admin.logout")
     */
    public function logoutAction()
    {}
}

