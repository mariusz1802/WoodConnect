<?php
namespace w3des\AdminBundle\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use w3des\AdminBundle\Entity\User;
use w3des\AdminBundle\Form\Type\UserType;
use w3des\AdminBundle\Entity\DownloadStat;

/**
 * @Route("/downloads")
 */
class DownloadStatsController extends Controller
{

    /**
     * @Route("/", name="admin.downloads")
     */
    public function usersAction()
    {
        return $this->render('w3desAdminBundle:DownloadStat:users.html.twig', array(
        ));
    }

    /**
     * @Route("/list.json", name="admin.downloads.json")
     */
    public function jsonAction(Request $request)
    {
        $repo = $this->getDoctrine()
        ->getManager()
        ->getRepository(DownloadStat::class);
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


        $node = $this->get('nodes');
        foreach ($paginator as $n) {
            /** @var \w3des\AdminBundle\Entity\Node $model */
            $model = $n->getNode();
            $url = '<a href="' . $node->getUrl($model->getRootModule()->getNode()) . '" target="_BLANK">' . $node->getVariable($n->getNode(), 'name') . '</a>';
            $data = [
                'id' => $n->getId(),
                'firstName' => $n->getFirstName(),
                'lastName' => $n->getLastName(),
                'phone' => $n->getPhone(),
                'email' => $n->getEmail(),
                'createdAt' => $n->getCreatedAt()->format('Y-m-d H:i:s'),
                'node' => $url
            ];
            $res[] = $data;
        }
        $response['data'] = $res;

        return new JsonResponse($response);
    }

    /*
     * @Route("/add", name="admin.downloads.add")
     *
    public function addAction(Request $request)
    {
        return $this->form(new DownloadStat(), $request);
    }

    /*
     * @Route("/{id}/edit", name="admin.downloads.edit")
     *
    public function editAction(DownloadStat $user, Request $request)
    {
        return $this->form($user, $request);
    }

    public function form(DownloadStat $user, Request $request)
    {
        $form = $this->createForm(DownloadStatType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            if ($form->get('plainPassword')->getData()) {
                $pass = $this
                ->get('security.password_encoder')
                ->encodePassword($user, $form->get('plainPassword')->getData());
                $user->setPassword($pass);
            }
            $em->persist($user);
            $em->flush();
            $this->get('session')
            ->getFlashBag()
            ->set('info', 'Zapisano pomyślnie');


            return $this->redirectToRoute('admin.users.edit', ['id' => $user->getId()]);
        }

        return $this->render('w3desAdminBundle:DownloadStat:form.html.twig', array(
            'form' => $form->createView(),
            'model' => $user
        ));
    }
    */

    /**
     * @Route("/{id}/remove", name="admin.downloads.remove")
     */
    public function removeAction(DownloadStat $user)
    {
        if ($user->getId() == $this->getUser()->getId()) {
            $this->get('session')
            ->getFlashBag()
            ->set('error', 'Nie można usunąć samego siebie');
        } else {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
            $this->get('session')
            ->getFlashBag()
            ->set('info', 'Zapisano pomyślnie');


        }
            return $this->redirectToRoute('admin.users');
    }

}

