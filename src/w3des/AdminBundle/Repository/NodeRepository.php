<?php
namespace w3des\AdminBundle\Repository;

use Doctrine\ORM\EntityRepository;

class NodeRepository extends EntityRepository
{

    public function findByPath($path, $locale)
    {
        $ex = $this->_em->createQuery('select n from w3desAdminBundle:Node n inner join n.url u where u.path = :path and u.locale = :locale')
            ->setParameters([
            'path' => $path,
            'locale' => $locale
        ])->execute();

        return count($ex) ? $ex[0] : null;
    }
}

