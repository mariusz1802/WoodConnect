<?php
namespace w3des\AdminBundle\Repository;

use Doctrine\ORM\EntityRepository;

class SettingsRepository extends EntityRepository
{

    public function findByNames(array $names, array $locales)
    {
        return $this->findBy([
            'name' => $names,
            'locale' => $locales
        ], [
            'name' => 'asc',
            'locale' => 'asc',
            'pos' => 'asc'
        ]);
    }
}

