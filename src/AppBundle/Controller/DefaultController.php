<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use w3des\AdminBundle\Entity\Node;
use w3des\AdminBundle\Model\NodeModuleControllerInterface;
use ZendSearch\Lucene\Analysis\Analyzer\Analyzer;
use ZendSearch\Lucene\Analysis\Analyzer\Common\TextNum;
use ZendSearch\Lucene\Search\QueryParser;
use ZendSearch\Lucene\Search\Query\Phrase;
use w3des\AdminBundle\Entity\DownloadStat;
use w3des\AdminBundle\Entity\PageUser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Knp\Menu\MenuItem;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {

        return $this->addCookie($this->render('default/index.html.twig'));
    }

    /**
     * @ParamConverter("node", converter="node")
     */
    public function nodeAction(Request $request, Node $node)
    {
	    $main = $this->get('knp_menu.menu_provider')->get('app.menu');
        $cfg = $this->get('nodes')->getCfg()[$node->getType()];
        if ($cfg['redirect_empty'] && count($this->get('nodes')->getSectionModules($node, $cfg['redirect_empty'])) == 0 && $node->getChildren()) {
            foreach ($node->getChildren() as $child) {
                if ($child->getUrl()) {
                    return $this->redirect($this->get('nodes')->getUrl($child));
                }
            }
        }
        if ($request->request->has('_module')) {
            foreach ($node->getModules() as $mod) {
                if ($mod->getId() == $request->request->get('_module')) {
                    $module = $this->get('nodes')->getModule($mod->getType());

                    if ($module instanceof NodeModuleControllerInterface) {
                        $resp = $module->control($mod, $request);
                        if ($resp) {
                            return $resp;
                        }
                    }
                    break;
                }
            }
        }
        if (\in_array($node->getType(), ['page', 'product'])) {
           $redirect = $this->get('nodes')->getVariable($node, 'redirect');

           if($redirect) {
               return $this->redirect($redirect);
           }
		}
		if ($node->getType() == 'product') {
            $cat = $this->get('nodes')->getVariable($node, 'category');
      
            $par = $this->getDoctrine()->getRepository(Node::class)->find($cat);
            
            $list = $this->findByCategory($main, $cat);
		
           
            if ($list) {
                $list->setCurrent(true);
            }
            
        } else {
            $list = $this->findByList($main, $node->getType());
            if ($list) {
                $list->setCurrent(true);
            }
        }

        return $this->addCookie($this->render('default/node_' . $node->getType() . '.html.twig', [
            'node' => $node
        ]));
    }
    
    private function findByList(MenuItem $menu, $type)
    {
        if ($menu->getExtra('node')) {
            $mods = $menu->getExtra('node')->findModules(ListModule::class);
            foreach ($mods as $mod) {
                if ($this->get('nodes')->getVariable($mod, 'type') == $type) {
                    return $menu;
                }
            }
        }
        foreach ($menu->getChildren() as $child) {
            $res = $this->findByList($child, $type);
            if ($res) {
                return $res;
            }
        }

        return null;
    }
    
    private function findByCategory(MenuItem $menu, $cat)
    {
        if ($menu->getExtra('node')) {
            $mods = $menu->getExtra('node')->findModules(\w3des\AdminBundle\NodeModule\ProductsModule::class);
            foreach ($mods as $mod) {
	        
                if ($this->get('nodes')->getVariable($mod, 'category') == $cat) {
                    return $menu;
                }
            }
        }
        foreach ($menu->getChildren() as $child) {
            $res = $this->findByCategory($child, $cat);
            if ($res) {
                return $res;
            }
        }

        return null;
    }

    private function findMenu(MenuItem $menu, Node $node)
    {
        if ($menu->getName() == $node->getId()) {
            return $menu;
        }
        foreach ($menu->getChildren() as $child) {
            $res = $this->findMenu($child, $node);
            if ($res !== false) {
                return $res;
            }
        }

        return false;
    }

    private function addCookie(Response $resp) {
        if($this->get('settings')->get('popup')) {
            $resp->headers->setCookie(new Cookie('visited', $this->get('settings')->get('popup')['path'], time()+3600*24));
        }
        return $resp;
    }

    /**
     * @Route("/_download/{id}", name="download")
     * @Method("GET")
     */
    public function downloadAction(Node $node)
    {
        if($node->getType() != 'file') {
            throw $this->createAccessDeniedException();
        }
        $stat = new DownloadStat();
        $user = $this->get('session')->get('_user_data', null);
        if (!$user) {
            $user = $this->getUser();
        }
        if ($user) {
        $stat->setEmail($user->getEmail());
        $stat->setFirstName($user->getFirstName());
        $stat->setLastName($user->getLastName());
        $stat->setNode($node);
        $stat->setEmail($user->getEmail());
        $stat->setPhone($user->getPhone());
       
        $em = $this->getDoctrine()->getManager();
        $em->persist($stat);
        $em->flush();
        try {
            $this->sendMail($user, $node);
        } catch(\Exception $e) {
            //ignore
        }
        }
         $response = new BinaryFileResponse($this->getParameter('upload.path') . $this->get('nodes')->getVariable($node, 'file')['path']);
        $response->setContentDisposition('attachment', $this->get('nodes')->getVariable($node, 'name'));

        

        return $response;
    }

    private function sendMail(PageUser $user, Node $node)
    {
        if (!$this->get('settings')->get('mail_download_to')) {
            return;
        }
        $fileName = $this->get('nodes')->getVariable($node, 'name');
        $nodeTitle = $this->get('nodes')->getVariable($node->getRootModule()->getNode(), 'title');
        $url = $this->get('nodes')->getUrl($node->getRootModule()->getNode());

        $msg = \Swift_Message::newInstance($user->getId() ? 'Pobranie pliku' : 'Pobranie pliku bez konta', "Nowe pobranie w systemie:
Nazwa pliku: " . $fileName. "
Nazwa strony: " . $nodeTitle . "
Adres strony: " . $url . "

Pobierający:
Imię i nazwisko: " . $user . "
Telefon: " . $user->getPhone() . "
Email: " . $user->getEmail() . "

--
optident.pl
", 'text/plain', 'utf-8');
        foreach (\explode(',', $this->get('settings')->get('mail_download_to') ?: $this->get('settings')->get('mail_download_to')) as $m) {
            $msg->addTo(trim($m));
        }
        $msg->setFrom($this->get('settings')->get('mail_from'), $this->get('settings')->get('mail_from_name'));

        $this->get('mailer')->send($msg);
    }

    /**
     * @Route("/search", name="search")
     * @Method("GET")
     */
    public function searchAction(Request $request)
    {
        if (!$request->query->get('query')) {
            return $this->redirect($request->headers->get('referer'));
        }
        Analyzer::setDefault(new TextNum());
        //$q = new Phrase(explode(' ', $request->query->get('query')));
        $q = QueryParser::parse($request->query->get('query') . '~', 'UTF-8');
        $documents = $this->get('ivory_lucene_search')->getIndex('nodes')->find($q);

        // Access finded datas
        $result = [];
        $em = $this->get('doctrine.orm.default_entity_manager');
        foreach ($documents as $document) {
            $node = $em->find(Node::class, $document->node_id);
            if ($node) {
                $result[] = $node;
            }
        }

        return $this->render('default/search.html.twig', [
            'result' => $result
        ]);
    }
}
