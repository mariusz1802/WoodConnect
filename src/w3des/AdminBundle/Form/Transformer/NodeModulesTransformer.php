<?php
namespace w3des\AdminBundle\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Collections\Collection;

class NodeModulesTransformer implements DataTransformerInterface
{

    /**
     * {@inheritDoc}
     */
    public function transform($value)
    {
        if ($value === null) {
            return $value;
        }
        if($value instanceof Collection) {
            return $value->toArray();
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function reverseTransform($value)
    {
        if ($value === null) {
            return $value;
        }

        return $value;
    }
}

