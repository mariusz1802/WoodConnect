<?php
namespace w3des\NewsletterBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use w3des\NewsletterBundle\Entity\NewsletterEmail;
use Ramsey\Uuid\Uuid;

class NewsletterController extends Controller
{
    /**
     * @Route("/subscribe", name="newsletter.subscribe")
     */
    public function subscribeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(NewsletterEmail::class);
        $email = trim(($request->get('email')));
        if ($request->get('direct')) {
            $email = \base64_decode($email);
        } else  {
            $email = \strtolower($email);
        }
        if ($request->get('remove')) {
            $model = $repo->findOneBy(['email' => $email, 'locale' => $request->getLocale()]);
            if ($model) {
                $em->remove($model);
                $em->flush();
                $this->get('session')->getFlashBag()->add('newsletter-flash', 'Usunięto pomyślnie');
            } else {
                $this->get('session')->getFlashBag()->add('newsletter-flash', 'Nie znaleziono adresu e-mail');
            }

        } else {

            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                if ($repo->findOneBy(['email' => $email, 'locale' => $request->getLocale()]) == null) {
                    $model = new NewsletterEmail();
                    $model->setEmail($email);
                    $model->setLocale($request->getLocale());
                    $model->setId(Uuid::uuid4()->toString());
                    $model->setCreatedAt(new \DateTime());
                    $em->persist($model);
                    $em->flush();
                    $this->get('session')->getFlashBag()->add('newsletter-flash', 'Zostałeś dodany do newslettera');
                } else {
                    $this->get('session')->getFlashBag()->add('newsletter-flash', 'Jesteś już użytkownikiem newslettera');
                }
            } else {
                $this->get('session')->getFlashBag()->add('newsletter-flash', 'Niepoprawny adres e-mail');
            }

        }

        return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('homepage'));
    }
}

