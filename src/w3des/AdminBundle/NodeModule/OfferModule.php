<?php
namespace w3des\AdminBundle\NodeModule;

use w3des\AdminBundle\Entity\NodeModule;

class OfferModule extends ListModule
{
    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'Oferty';
    }

    /**
     * {@inheritDoc}
     */
    public function render(NodeModule $mod)
    {
        return $this->twig->render('nodes/news.html.twig', [
            'module' => $mod,
            'type' => 'offer'
        ]);
    }
}

