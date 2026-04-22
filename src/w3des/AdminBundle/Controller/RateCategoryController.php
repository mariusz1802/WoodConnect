<?php
namespace w3des\AdminBundle\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use w3des\AdminBundle\Entity\User;
use w3des\AdminBundle\Form\Type\RateCategoryType;
use w3des\AdminBundle\Form\Type\UserType;
use w3des\AdminBundle\Entity\RateCategory;

/**
 * @Route("/rate/category")
 */
class RateCategoryController extends Controller
{

    /**
     * @Route("/", name="admin.rate.category")
     */
    public function indexAction()
    {
        return $this->render('w3desAdminBundle:RateCategory:index.html.twig', array(
        ));
    }

    /**
     * @Route("/grid.json", name="admin.rate.category.json")
     */
    public function jsonAction(Request $request)
    {
        $repo = $this->getDoctrine()
        ->getManager()
        ->getRepository(RateCategory::class);
        $qb = $repo->createQueryBuilder('a');

        if ($request->query->get('name')) {
            $qb->andWhere('lower(a.name) like lower(:em)')->setParameter('em', $request->get('name'));
        }

        if ($request->query->has('sortField')) {
            $qb->orderBy('a.' . $request->query->get('sortField'), $request->query->get('sortOrder'));
        } else {
            $qb->orderBy('a.position', 'asc');
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
            $data = [
                'id' => $n->getId(),
                'name' => $n->getName(),
                'position' => $n->getPosition()
            ];
            $res[] = $data;
        }
        $response['data'] = $res;

        return new JsonResponse($response);
    }

    /**
     * @Route("/add", name="admin.rate.category.add")
     */
    public function addAction(Request $request)
    {
        return $this->form(new RateCategory(), $request);
    }

    /**
     * @Route("/{id}/edit", name="admin.rate.category.edit")
     */
    public function editAction(RateCategory $user, Request $request)
    {
        return $this->form($user, $request);
    }

    public function form(RateCategory $model, Request $request)
    {
        $form = $this->createForm(RateCategoryType::class, $model);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($model);
            $em->flush();
            $this->get('session')
            ->getFlashBag()
            ->set('info', 'Zapisano pomyślnie');


            return $this->redirectToRoute('admin.rate.category.edit', ['id' => $model->getId()]);
        }

        return $this->render('w3desAdminBundle:RateCategory:form.html.twig', array(
            'form' => $form->createView(),
            'model' => $model
        ));
    }


    /**
     * @Route("/{id}/remove", name="admin.rate.category.remove")
     */
    public function removeAction(RateCategory $user)
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
            return $this->redirectToRoute('admin.rate.category');
    }

}

