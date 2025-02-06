<?php

namespace App\Repository;

use App\Entity\Vehicule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vehicule>
 */
class VehiculeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicule::class);
    }

    public function findByFilters($marque = null, $prixMax = null, $disponibilite = null)
    {
        $queryBuilder = $this->createQueryBuilder('v');

        if ($marque) {
            $queryBuilder->andWhere('v.marque LIKE :marque')
                ->setParameter('marque', '%'.$marque.'%');
        }

        if ($prixMax) {
            $queryBuilder->andWhere('v.prix_journalier <= :prixMax')
                ->setParameter('prixMax', $prixMax);
        }

        if ($disponibilite !== null) {
            $queryBuilder->andWhere('v.disponibilite = :disponibilite')
                ->setParameter('disponibilite', $disponibilite === "1");
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
