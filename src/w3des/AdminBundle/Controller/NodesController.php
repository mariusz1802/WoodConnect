<?php
namespace w3des\AdminBundle\Controller;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use w3des\AdminBundle\Entity\Node;
use w3des\AdminBundle\Entity\NodeUrl;
use w3des\AdminBundle\Form\Type\NodeModulesType;
use w3des\AdminBundle\Form\Type\TabType;
use w3des\AdminBundle\Form\Type\ValueListType;
use w3des\AdminBundle\Model\NodeModel;
use w3des\AdminBundle\Entity\NodeVariable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Doctrine\ORM\Query\Expr\Join;
use ZendSearch\Lucene\Search\Query\Term;
use ZendSearch\Lucene\Analysis\Analyzer\Analyzer;
use ZendSearch\Lucene\Analysis\Analyzer\Common\TextNum\CaseInsensitive;
use ZendSearch\Lucene\Search\QueryParser;
use ZendSearch\Lucene\Search\Query\MultiTerm;

/**
 * @Route("/nodes")
 */
class NodesController extends Controller
{

    /**
     * @Route("/{type}/list", name="admin.node")
     * @Method("GET")
     */
    public function indexAction($type, $pageLocale, Request $request)
    {
        $cfg = $this->get('nodes')->getCfg()[$type];

        $repo = $this->getDoctrine()
            ->getManager()
            ->getRepository(Node::class);
        $qb = $repo->createQueryBuilder('a');
        $qb->andWhere('a.type = :type and a.locale = :locale and a.parent is null')
            ->setParameter('type', $type)
            ->setParameter('locale', $pageLocale);
        $qb->join('a.variables', 'v');
        $qb->orderBy('a.pos', 'ASC');


        return $this->render('w3desAdminBundle:Nodes:index.html.twig', [
            'items' => $cfg['sortable'] ? $qb->getQuery()
                ->execute() : [],
            'cfg' => $cfg,
            'fields' => $this->get('nodes')
                ->getFields($type),
            'type' => $type,
            'values' => $this->get('values')
        ]);
    }

    /**
     * @Route("/{type}/list")
     * @Method("POST")
     */
    public function saveOrderAction($type, $pageLocale, Request $request)
    {
        $em = $this->getDoctrine()
        ->getManager();
        $repo = $em
        ->getRepository(Node::class);
        $em->beginTransaction();
        foreach ($request->request->get('tree') as $tr) {
            $tmp = $repo->find($tr['id']);
            if ($tr['parent'] && $tr['parent'] !== 'null') {
                $tmp->setParent($em->getReference(Node::class, $tr['parent']));
            } else {
                $tmp->setParent(null);
            }
            $tmp->setPos($tr['pos']);
            $em->persist($tmp);
            $em->flush();
            $em->detach($tmp);
        }
        $em->commit();

        $this->get('session')
        ->getFlashBag()
        ->set('info', 'Zapisano pomyślnie');
        return $this->redirect($this->generateUrl('admin.node', [
            'type' => $type
        ]));
    }

    /**
     * @Route("/{type}.json", name="admin.node.json")
     */
    public function jsonAction($type, $pageLocale, Request $request)
    {
        $cfg = $this->get('nodes')->getCfg()[$type];
        $fields = $this->get('nodes')->getFields($type);
        $repo = $this->getDoctrine()
            ->getManager()
            ->getRepository(Node::class);
        $qb = $repo->createQueryBuilder('a');
        $qb->andWhere('a.type = :type and a.locale = :locale')
            ->setParameter('type', $type)
            ->setParameter('locale', $pageLocale);
        $num = 0;
        $qb->orderBy('a.id', 'desc');
        foreach ($cfg['grid'] as $field) {
            $def = $fields[$field];
            if ($request->query->get($field) || $request->query->get('sortField') == $field) {
                $val = $request->query->get($field);
                $qb->innerJoin('a.variables', 'v_' . $field . $num, Join::WITH, 'v_' . $field . $num . '.name = \'' . $field . '\'');
                if($request->query->has($field)) {
                    if ($def->storeType == 'string' || $def->storeType == 'text') {
                        $val = '%' . trim($val) . '%';
                        $qb->andWhere('lower(v_' . $field . $num. '.' . NodeVariable::getFieldName($def->storeType). ') like lower(:val' . $num . ')')->setParameter('val' . $num, $val);
                    } else {
                        if ($def->storeType == 'bool') {
                            $val = $val == 'true' ? 1 : 0;
                        }
                        $qb->andWhere('v_' . $field . $num. '.' . NodeVariable::getFieldName($def->storeType). ' = :val' . $num)->setParameter('val' . $num, $val);
                    }
                }
                if ($request->query->get('sortField') == $field) {
                    if ($def->storeType == 'string' || $def->storeType == 'text') {
                        $qb->orderBy('lower(v_' . $field . $num. '.' . NodeVariable::getFieldName($def->storeType) . ')', $request->query->get('sortOrder'));
                    } else {
                        $qb->orderBy('v_' . $field . $num. '.' . NodeVariable::getFieldName($def->storeType), $request->query->get('sortOrder'));
                    }
                }
            }

            $num++;
        }

        $qb->join('a.variables', 'v')->addSelect('v');

        $res = [];
        $vals = $this->get('values');
        $paginator = new Paginator($qb->getQuery(), true);
        $response = [
            'itemsCount' => count($paginator)
        ];
        $paginator->getQuery()
            ->setMaxResults($request->get('pageSize'))
            ->setFirstResult($request->get('pageSize') * ($request->get('pageIndex') - 1));


        foreach ($paginator as $n) {
            $data = [
                'id' => $n->getId()
            ];
            foreach ($cfg['sections'] as $sec) {
                foreach ($cfg['grid'] as $name) {
                    $v = $vals->collectValues($n->getVariables(), $name, '');
                    if ($v) {
                        if ($v instanceof \DateTime) {
                            $v = $v->format('Y-m-d H:i:s');
                        }
                        $data[$name] = $v;
                    } else {
                        $data[$name] = null;
                    }
                }
            }
            $res[] = $data;
        }
        $response['data'] = $res;

        return new JsonResponse($response);
    }

    /**
     * @Route("/{type}/add", name="admin.node.add")
     */
    public function addAction($type, Request $request)
    {
        return $this->form($request, $type);
    }

    /**
     * @Route("/{type}/{id}/edit", name="admin.node.edit")
     */
    public function editAction(Node $node, $type, Request $request)
    {
        return $this->form($request, $type, $node);
    }

    /**
     * @Route("/{type}/{id}/remove", name="admin.node.remove")
     */
    public function removeAction(Node $node, $type, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->beginTransaction();

        $index = $this->get('ivory_lucene_search')->getIndex('nodes');
        if ($this->get('nodes')->getCfg()[$type]['index']) {
            $doc = $index->find(new Term(new \ZendSearch\Lucene\Index\Term($node->getId().'', 'node_id')));
            if (count($doc)) {
                $index->delete($doc[0]->id);
            }

        }
        $em->remove($node);
        $em->flush();
        $index->commit();
        $index->optimize();
        $em->commit();

        $this->get('session')
        ->getFlashBag()
        ->set('info', 'Zapisano pomyślnie');


        return $this->redirect($this->generateUrl('admin.node', [
            'type' => $type
        ]));
    }

    protected function form(Request $request, $type, Node $node = null)
    {
        $cfg = $this->get('nodes')->getCfg()[$type];
        $em = $this->get('doctrine.orm.default_entity_manager');

        if (! $node) {
            $node = new Node();
            $node->setType($type);
            $node->setLocale($cfg['locale'] ? $request->attributes->get('_page_locale') : '');

            if ($request->query->has('parent')) {
                $node->setParent($em->getReference(Node::class, $request->query->get('parent')));
            }
            $node->setPos(0);
        }

        $formModel = new NodeModel($node, $cfg, $cfg['locale'] ? [
            $node->getLocale()
        ] : $this->getParameter('page.locales'));

        $fb = $this->createFormBuilder($formModel);
        $fb->add('tabPosition', HiddenType::class, [
            'mapped' => false
        ]);
        foreach ($cfg['sections'] as $sectionName => $sectionCfg) {
            $section = $fb->add('_sec_' . $sectionName, TabType::class, [
                'label_format' => 'node.section.' . $sectionName,
                'translation_domain' => 'admin'
            ])->get('_sec_' . $sectionName);
            if (count($sectionCfg['fields'])) {
                $section->add('fields', ValueListType::class, [
                    'label' => false,
                    'value_list' => $formModel->getFields(),
                    'label_prefix' => 'node.field.',
                    'sections' => $sectionCfg['fields']
                ]);
            }

            if (count($sectionCfg['modules'])) {
                $section->add('modules', NodeModulesType::class, array(
                    'modules' => $sectionCfg['modules'],
                    'section' => $sectionName,
                    'property_path' => 'modules[' . $sectionName . ']'
                ));
            }
        }

        $form = $fb->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            if ($cfg['url']) {

                if (! $node->getUrl()) {
                    $url = new NodeUrl();
                    $url->setLocale($node->getLocale());
                    $url->setNode($node);
                    $node->setUrl($url);
                }
                $em->persist($node->getUrl());
                $url = $formModel->getFields()->url;
                $source = $formModel->getFields()->{$cfg['url']};

                if (! $url) {
                    $s = new Slugify();
                    $url = $s->slugify($source . '');
                }
                $count = 0;
                $q = $em->createQuery('select u from w3desAdminBundle:NodeUrl u where u.locale = :locale and u.slug = :slug and (:id is null or u.id != :id)')->setParameters([
                    'slug' => $url . ($count ? '-' . $count : ''),
                    'locale' => $node->getLocale(),
                    'id' => $node->getUrl()
                        ->getId()
                ]);
                while (count($q->execute())) {
                    $count ++;
                    $q->setParameters([
                        'slug' => $url . ($count ? '-' . $count : ''),
                        'locale' => $node->getLocale(),
                        'id' => $node->getUrl()
                            ->getId()
                    ]);
                }
                $node->getUrl()->setSlug($url . ($count ? '-' . $count : ''));
                $node->getUrl()->setPath($node->getUrl()
                    ->getSlug());
                $formModel->getFields()->url = $node->getUrl()->getSlug();
            }
            $this->get('values')->handleValues($formModel->getFields(), function () use ($node) {
                $var = new NodeVariable();
                $var->setNode($node);
                $node->getVariables()
                    ->add($var);

                return $var;
            }, function () {}, function ($var) use ($node) {
                $node->getVariables()
                    ->removeElement($var);
            });
            $em->beginTransaction();
            $index = $this->get('ivory_lucene_search')->getIndex('nodes');

            $em->persist($node);
            $em->flush();
            $index->commit();
            if ($this->get('nodes')->getCfg()[$type]['index']) {
                $doc = $index->find(new Term(new \ZendSearch\Lucene\Index\Term($node->getId().'', 'node_id')));
                if (count($doc)) {
                    $index->delete($doc[0]->id);
                }
                $index->addDocument($this->get('nodes')->getDocument($node));
            }
            $index->optimize();

            $em->commit();

            $this->get('session')
                ->getFlashBag()
                ->set('info', 'Zapisano pomyślnie');

            return $this->redirect($this->generateUrl('admin.node.edit', [
                'type' => $type,
                'id' => $node->getId()
            ]) . '#' . $form->get('tabPosition')
                ->getData());
        }

        return $this->render('w3desAdminBundle:Nodes:form.html.twig', [
            'type' => $type,
            'cfg' => $cfg,
            'node' => $node,
            'form' => $form->createView()
        ]);
    }
}

