<?php
namespace w3des\AdminBundle\Request;

use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use w3des\AdminBundle\Entity\Node;
use Doctrine\ORM\EntityManager;

class NodeConverter implements ParamConverterInterface
{

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritDoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $request->attributes->set($configuration->getName(), $this->em->getRepository(Node::class)
            ->findByPath($request->attributes->get('path'), $request->getLocale()));
    }

    /**
     * {@inheritDoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getClass() == Node::class;
    }
}

