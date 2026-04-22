<?php
namespace w3des\NewsletterBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use w3des\NewsletterBundle\Util;
use w3des\NewsletterBundle\Entity\NewsletterEmail;
use w3des\NewsletterBundle\Entity\NewsletterContent;
use w3des\NewsletterBundle\Form\NewsletterType;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\EmailValidator;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/newsletter")
 */
class AdminController extends Controller
{

    /**
     * @Route("/subscribers", name="admin.newsletter.subscribers")
     */
    public function subscribersAction()
    {
        return $this->render('w3desNewsletterBundle:Admin:subscribers.html.twig');
    }

    /**
     * @Route("/subscribers.csv", name="admin.newsletter.subscribers.export")
     */
    public function subscribersExportAction($pageLocale)
    {
        $subsribers = $this->get('doctrine.orm.default_entity_manager')
            ->createQuery('select e.email from w3desNewsletterBundle:NewsletterEmail e where e.locale = :loc')
            ->setParameter('loc', $pageLocale)
            ->getScalarResult();
        return new StreamedResponse(function () use ($subsribers) {
            echo "email\r\n";
            foreach ($subsribers as $sub) {
                echo $sub['email'] . "\r\n";
            }
        }, 200, [
            'Content-Type' => 'text/csv',
            'Cache-Control' => 'private',
            'Content-Disposition' => 'attachment; filename=export_email_' . $pageLocale . date('-YmdHis'). '.csv'
        ]);
    }

    /**
     * @Route("/subscribers/import", name="admin.newsletter.subscribers.import")
     */
    public function subscribersImportAction(Request $request, $pageLocale)
    {
        $fb = $this->createFormBuilder();
        $fb->add('file',FileType::class, [
            'label' => 'Plik CSV'
        ]);
        $form = $fb->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $f */
            $f = $form->get('file')->getData();
            if ($f && $f->getSize() > 0) {
                $fileName = \tempnam(\sys_get_temp_dir(), 'email');
                $f->move(\dirname($fileName), \basename($fileName));
                $msg = $this->get('newsletter')->import(pageLocale, $fileName);
                unlink($fileName);
                $this->get('session')
                ->getFlashBag()
                ->add('info', $msg);
                return $this->redirectToRoute('admin.newsletter.subscribers');
            }
        }

        return $this->render('w3desNewsletterBundle:Admin:importSubscribers.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/subscribers.json", name="admin.newsletter.subscribers.json")
     */
    public function subscribersListAction(Request $request)
    {
        $qb = $this->getDoctrine()
            ->getManager()
            ->getRepository(NewsletterEmail::class)
            ->createQueryBuilder('qb');

        if ($request->query->get('sortField')) {
            $qb->orderBy('qb.' . $request->query->get('sortField'), $request->query->get('sortOrder'));
        }
        foreach (['email'] as $field) {
            if ($request->query->get($field)) {
                $qb->andWhere('lower(qb.' . $field . ') like lower(:s' . $field . ')')->setParameter('s' . $field, '%'.$request->query->get($field) .'%');
            }
        }

        $paginator = new Paginator($qb->getQuery(), true);
        $response = [
            'itemsCount' => count($paginator),
            'data' => []
        ];
        $paginator->getQuery()
            ->setMaxResults($request->get('pageSize'))
            ->setFirstResult($request->get('pageSize') * ($request->get('pageIndex') - 1));
        foreach ($paginator as $item) {
            $response['data'][] = [
                'id' => $item->getId(),
                'email' => $item->getEmail(),
                'locale' => $item->getLocale(),
                'createdAt' => $item->getCreatedAt()->format('Y-m-d H:i')
            ];
        }
        return new JsonResponse($response);
    }

    /**
     * @Route("/subscribers/{id}/remove", name="admin.newsletter.subscriber.remove")
     */
    public function subscriberRemoveAction(Request $request, NewsletterEmail $cnt)
    {
        $em = $this->get('doctrine.orm.default_entity_manager');
        $em->remove($cnt);
        $em->flush();

        $this->get('session')
        ->getFlashBag()
        ->set('info', 'Zapisano pomyślnie');
        return $this->redirect($this->generateUrl('admin.newsletter.subscribers'));
    }

    /**
     * @Route("/contents", name="admin.newsletter.contents")
     */
    public function contentsAction()
    {
        return $this->render('w3desNewsletterBundle:Admin:contents.html.twig');
    }

    /**
     * @Route("/contents.json", name="admin.newsletter.contents.json")
     */
    public function contentsListAction(Request $request)
    {
        $qb = $this->getDoctrine()
            ->getManager()
            ->getRepository(NewsletterContent::class)
            ->createQueryBuilder('qb');

        if ($request->query->get('sortField')) {
            $qb->orderBy('qb.' . $request->query->get('sortField'), $request->query->get('sortOrder'));
        }

        $paginator = new Paginator($qb->getQuery(), true);
        $response = [
            'itemsCount' => count($paginator),
            'data' => []
        ];
        $paginator->getQuery()
            ->setMaxResults($request->get('pageSize'))
            ->setFirstResult($request->get('pageSize') * ($request->get('pageIndex') - 1));
        foreach ($paginator as $item) {
            $response['data'][] = [
                'title' => $item->getTitle(),
                'id' => $item->getId(),
                'locale' => $item->getLocale(),
                'createdAt' => $item->getCreatedAt()->format('Y-m-d H:i'),
                'sendAt' => $item->getSendAt() ? $item->getSendAt()->format('Y-m-d H:i') : '-'
            ];
        }
        return new JsonResponse($response);
    }

    /**
     * @Route("/contents/add", name="admin.newsletter.content.add")
     */
    public function contentAddAction(Request $request, $pageLocale)
    {
        $cnt = new NewsletterContent();
        $cnt->setLocale($pageLocale);

        return $this->form($request, $cnt);
    }

    protected function form(Request $request, NewsletterContent $model)
    {
        $em = $this->get('doctrine.orm.default_entity_manager');

        $form = $this->createForm(NewsletterType::class, $model);
        if ($model->getId()) {
            $form->get('test_email')->setData($this->get('session')
                ->get('test_email', $this->getUser()
                ->getEmail()));
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            if ($request->get('send')) {
                if ($form->get('test_email')->getData()) {
                    $this->get('session')->set('test_email', $form->get('test_email')
                        ->getData());
                }
                $mail = $form->get('test_email')->getData();
                if ($mail) {
                    $this->get('newsletter')->send($model, $mail);
                    $this->get('session')
                        ->getFlashBag()
                        ->add('info', ' Wysłano wiadomość testową');
                } else {
                    $model->setSendAt(new \DateTime());
                    $model->setSent(false);
                    $this->get('session')
                        ->getFlashBag()
                        ->add('info', ' Mail przekazany do wysyłki');
                }
            }
            $em->persist($model);
            $em->flush();

            $this->get('session')
                ->getFlashBag()
                ->add('info', 'Zapisano pomyślnie');

            return $this->redirect($this->generateUrl('admin.newsletter.content.edit', [
                'id' => $model->getId()
            ]));
        }

        return $this->render('w3desNewsletterBundle:Admin:form.html.twig', [
            'model' => $model,
            'form' => $form->createView()
        ]);
    }





    /**
     * @Route("/contents/{id}/remove", name="admin.newsletter.content.remove")
     */
    public function contentRemoveAction(Request $request, NewsletterContent $cnt)
    {
        $em = $this->get('doctrine.orm.default_entity_manager');
        $em->remove($cnt);
        $em->flush();

        $this->get('session')
            ->getFlashBag()
            ->set('info', 'Zapisano pomyślnie');
        return $this->redirect($this->generateUrl('admin.newsletter.contents'));
    }

    /**
     * @Route("/contents/{id}/edit", name="admin.newsletter.content.edit")
     */
    public function contentEditAction(Request $request, NewsletterContent $cnt)
    {
        return $this->form($request, $cnt);
    }
}

