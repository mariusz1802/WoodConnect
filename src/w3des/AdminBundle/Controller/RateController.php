<?php
namespace w3des\AdminBundle\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use w3des\AdminBundle\Entity\User;
use w3des\AdminBundle\Form\Type\RateType;
use w3des\AdminBundle\Form\Type\UserType;
use w3des\AdminBundle\Entity\Rate;

/**
 * @Route("/rate")
 */
class RateController extends Controller
{

    /**
     * @Route("/", name="admin.rate")
     */
    public function indexAction()
    {
        return $this->render('w3desAdminBundle:Rate:index.html.twig', array(
        ));
    }

    /**
     * @Route("/list.json", name="admin.rate.json")
     */
    public function jsonAction(Request $request)
    {
        $repo = $this->getDoctrine()
        ->getManager()
        ->getRepository(Rate::class);
        $qb = $repo->createQueryBuilder('a');

        if ($request->query->get('email')) {
            $qb->andWhere('lower(a.email) like lower(:em)')->setParameter('em', $request->get('email'));
        }
        if ($request->query->get('phone')) {
            $qb->andWhere('lower(a.phone) like lower(:em2)')->setParameter('em2', $request->get('phone'));
        }
        if ($request->query->get('firstName')) {
            $qb->andWhere('lower(a.firstName) like lower(:em3)')->setParameter('em3', $request->get('firstName'));
        }
        if ($request->query->get('email')) {
            $qb->andWhere('lower(a.lastName) like lower(:em4)')->setParameter('em4', $request->get('lastName'));
        }

        if ($request->query->has('sortField')) {
            $qb->orderBy('a.' . $request->query->get('sortField'), $request->query->get('sortOrder'));
        } else {
            $qb->orderBy('a.createdAt', 'desc');
        }

        $res = [];
        $paginator = new Paginator($qb->getQuery(), true);
        $response = [
            'itemsCount' => count($paginator)
        ];
        $paginator->getQuery()
        ->setMaxResults($request->get('pageSize'))
        ->setFirstResult($request->get('pageSize') * ($request->get('pageIndex') - 1));


        foreach ($paginator as $n) {
                /** @var \w3des\AdminBundle\Entity\Rate $n */
            $data = [
                'id' => $n->getId(),
                'createdAt' => $n->getCreatedAt()->format('Y-m-d H:i:s'),
                'email' => $n->getEmail(),
                'name' => $n->getName(),
                'confirmed' => $n->getConfirmed(),
                'approved' => $n->getApproved(),
                'value' =>$n->getValue(),
                'ip' => $n->getIp()
            ];
            $res[] = $data;
        }
        $response['data'] = $res;

        return new JsonResponse($response);
    }

    /*
     * @Route("/add", name="admin.rate.add")
     */
    public function addAction(Request $request)
    {
        return $this->form(new Rate(), $request);
    }

    /**
     * @Route("/{id}/edit", name="admin.rate.edit")
     */
    public function editAction(Rate $model, Request $request)
    {
        return $this->form($model, $request);
    }

    public function form(Rate $model, Request $request)
    {
        $form = $this->createForm(RateType::class, $model, ['admin' => true]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($model);
            $em->flush();
            $this->get('session')
            ->getFlashBag()
            ->set('info', 'Zapisano pomyślnie');


            return $this->redirectToRoute('admin.rate.edit', ['id' => $model->getId()]);
        }

        return $this->render('w3desAdminBundle:Rate:form.html.twig', array(
            'form' => $form->createView(),
            'model' => $model
        ));
    }


    /**
     * @Route("/{id}/remove", name="admin.rate.remove")
     */
    public function removeAction(Rate $model)
    {
            $em = $this->getDoctrine()->getManager();
            $em->remove($model);
            $em->flush();
            $this->get('session')
            ->getFlashBag()
            ->set('info', 'Zapisano pomyślnie');
            return $this->redirectToRoute('admin.rate');
    }

}

