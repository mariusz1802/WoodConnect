<?php
namespace w3des\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Cocur\Slugify\Slugify;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class DashboardController extends Controller
{

    /**
     * @Route("/", name="admin.home")
     */
    public function indexAction()
    {
        return $this->render('w3desAdminBundle:Dashboard:index.html.twig');
    }

    /**
     * @Route("/lang/{lang}", name="admin.lang")
     */
    public function langAction($lang)
    {
        $this->get('session')->set('_page_locale', $lang);

        return $this->redirect($this->generateUrl('admin.home'));
    }

    /**
     * @Route("/upload", name="admin.upload")
     * @Method("POST")
     */
    public function uploadAction(Request $request)
    {
        $uploadDir = \realpath($this->getParameter('upload.dir'));
        $dir = '/' . $request->request->get('dir') . '/' . date('Y') . '/' . date('m') . '/' . date('d');
        if (! \file_exists($uploadDir . $dir)) {
            \mkdir($uploadDir . $dir, 0777, true);
        }
        $tmp = new Slugify();
        $result = [];
        foreach ($request->files->get('files') as $f) {
            if ($f instanceof UploadedFile) {
                $orig = $source = $f->getClientOriginalName();
                if ($f->getClientOriginalExtension()) {
                    $source = substr($source, 0, strlen($source) - 1 - strlen($f->getClientOriginalExtension()));
                }
                $name = \uniqid('', true) . '_' . $tmp->slugify($source) . '.' . \strtolower($f->getClientOriginalExtension());
                $f->move($uploadDir . $dir, $name);
                \chmod($uploadDir . $dir . '/' . $name, 0666);
                $f = new \SplFileInfo($uploadDir . $dir . '/' . $name);
                $result[] = [
                    'path' => $dir . '/' . $name,
                    'name' => $name,
                    'origName' => $orig,
                    'thmb' => $this->get('liip_imagine.cache.manager')->getBrowserPath($this->getParameter('upload.path').$dir . '/' . $name, 'admin'),
                    'size' => $f->getSize(),
                    'url' => $request->getBaseUrl() . $this->getParameter('upload.path') . $dir . '/' . $name,
                    'deleteUrl' => false,
                    'deleteType' => 'DELETE'
                ];
            }
        }

        return new JsonResponse([
            'files' => $result
        ]);
    }
}

