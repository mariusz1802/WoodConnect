<?php
namespace w3des\AdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use w3des\AdminBundle\Entity\Setting;
use w3des\AdminBundle\Form\Type\ValueListType;
use w3des\AdminBundle\Model\ValueInterface;
use w3des\AdminBundle\Model\ValueList;
use w3des\AdminBundle\Service\Settings;

/**
 * @Route("/settings")
 */
class SettingsController extends Controller
{

    /**
     * @Route("/{group}", name="admin.settings")
     */
    public function groupAction($group, $pageLocale, Request $request)
    {
        $sett = $this->get('settings');
        if (! isset($sett->getSections()[$group])) {
            throw $this->createNotFoundException();
        }

        $sectionFields = $sett->getSections()[$group];
        $definitions = [];
        foreach ($sectionFields as $name) {
            if (is_array($name)) {
                foreach ($name as $sub) {
                    $definitions[$sub] = $sett->getField($sub);
                }
            } else {
                $definitions[$name] = $sett->getField($name);
            }
        }

        $list = new ValueList([
            $pageLocale
        ], $definitions);
        $em = $this->getDoctrine()
        ->getManager();
        $list->loadModels($em
            ->getRepository(Setting::class)
            ->findByNames(\array_keys($definitions), ['', $pageLocale]));
        $form = $this->createForm(ValueListType::class, $list, [
            'label_prefix' => 'settings.',
            'sections' => $sectionFields
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->get('values')->handleValues($list, function() {
                return new Setting();
            }, function(ValueInterface $model) use($em) {
                $em->persist($model);
            }, function(ValueInterface $model) use ($em) {
                $em->remove($model);
            });
            $em->flush();
            $this->get('session')
                ->getFlashBag()
                ->set('info', 'Zapisano pomyślnie');

            return $this->redirect($this->generateUrl('admin.settings', [
                'group' => $group
            ]));
        }

        return $this->render('w3desAdminBundle:Settings:group.html.twig', [
            'group' => $group,
            'form' => $form->createView()
        ]);
    }
}

