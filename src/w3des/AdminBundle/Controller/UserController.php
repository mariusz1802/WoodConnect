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

class UserController extends Controller
{

    /**
     * @Route("/users", name="admin.users")
     */
    public function usersAction()
    {
        return $this->render('w3desAdminBundle:User:users.html.twig', array(
        ));
    }

    /**
     * @Route("/users.json", name="admin.users.json")
     */
    public function jsonAction(Request $request)
    {
        $repo = $this->getDoctrine()
        ->getManager()
        ->getRepository(User::class);
        $qb = $repo->createQueryBuilder('a');

        if ($request->query->get('email')) {
            $qb->andWhere('lower(a.email) like lower(:em)')->setParameter('em', $request->get('email'));
        }

        if ($request->query->has('isEnabled')) {
            $qb->andWhere('e.isEnabled = :en')->setParameter('en', (bool)$request->get('isEnabled'));
        }
        if ($request->query->has('sortField')) {
            $qb->orderBy('a.' . $request->query->get('sortField'), $request->query->get('sortOrder'));
        } else {
            $qb->orderBy('a.email', 'asc');
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
                'email' => $n->getEmail(),
                'isEnabled' => $n->isEnabled()
            ];
            $res[] = $data;
        }
        $response['data'] = $res;

        return new JsonResponse($response);
    }

    /**
     * @Route("/add", name="admin.users.add")
     */
    public function addAction(Request $request)
    {
        return $this->form(new User(), $request);
    }

    /**
     * @Route("/{id}/edit", name="admin.users.edit")
     */
    public function editAction(User $user, Request $request)
    {
        return $this->form($user, $request);
    }

    public function form(User $user, Request $request)
    {
        $form = $this->createForm(UserType::class, $user);

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

        return $this->render('w3desAdminBundle:User:form.html.twig', array(
            'form' => $form->createView(),
            'model' => $user
        ));
    }

    /**
     * @Route("/{id}/remove", name="admin.users.remove")
     */
    public function removeAction(User $user)
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

