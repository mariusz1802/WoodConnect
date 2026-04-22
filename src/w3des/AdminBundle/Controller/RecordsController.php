<?php
namespace w3des\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use w3des\AdminBundle\Entity\Records;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use w3des\AdminBundle\Form\Type\RecordsType;

/**
 * @Route("/records")
 */
class RecordsController extends Controller
{

    /**
     * @Route("/", name="admin.records")
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/list.json", name="admin.records.json")
     */
    public function jsonAction(Request $request)
    {
        $qb = $this->get('doctrine.orm.default_entity_manager')
            ->getRepository(Records::class)
            ->createQueryBuilder('r');

        $qb->orderBy('r.createdAt', 'desc');
        $support = [
            'name',
            'practiceName',
            'phone',
            'email'
        ];
        $f = 0;
        foreach ($support as $field) {
            if ($request->query->get($field)) {
                $qb->andWhere('lower(r.' . $field . ') like lower(:search' . $f . ')')->setParameter('search' . $f, '%' . $request->query->get($field) . '%');
            }
            $f ++;
        }
        if ($request->query->get('sortField')) {
            $qb->orderBy($request->query->get('sortField'), $request->query->get('sortOrder'));
        }

        $paginator = new Paginator($qb->getQuery(), true);
        $res = [
            'itemsCount' => count($paginator)
        ];
        $paginator->getQuery()
            ->setMaxResults($request->get('pageSize'))
            ->setFirstResult($request->get('pageSize') * ($request->get('pageIndex') - 1));

        $list = [];
        /** @var \w3des\AdminBundle\Entity\Records $n */
        foreach ($paginator as $n) {
            $data = [
                'id' => $n->getId(),
                'name' => $n->getName(),
                'createdAt' => $n->getCreatedAt()->format('Y-m-d H:i'),
                'phone' => $n->getPhone(),
                'email' => $n->getEmail(),
                'practiceName' => $n->getPracticeName()
            ];
            $list[] = $data;
        }
        $res['data'] = $list;

        return new JsonResponse($res);
    }

    /**
     * @Route("/{id}/edit", name="admin.records.edit")
     * @Template()
     */
    public function editAction(Records $record, Request $request)
    {
        $form = $this->createForm(RecordsType::class, $record);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($record);
            $em->flush();

            $this->get('session')
                ->getFlashBag()
                ->add('info', 'Zapisano pomyślnie');

            return $this->redirectToRoute('admin.records');
        }

        return [
            'form' => $form->createView(),
            'model' => $record
        ];
    }

    /**
     * @Route("/{id}/remove", name="admin.records.remove")
     */
    public function removeAction(Records $record)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($record);
        $em->flush();

        $this->get('session')
            ->getFlashBag()
            ->add('info', 'Usunięto pomyślnie');
        return $this->redirectToRoute('admin.records');
    }
}

