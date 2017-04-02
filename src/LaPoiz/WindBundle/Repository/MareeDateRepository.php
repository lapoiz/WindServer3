<?php

namespace LaPoiz\WindBundle\Repository;

use Doctrine\ORM\EntityRepository;

use LaPoiz\WindBundle\Entity\Spot;

/**
 * MareeDateRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MareeDateRepository extends EntityRepository
{
    // JAMAIS UTILISE ????????????????
    public function findLast($spot)
    {
        $queryBuilder = $this->createQueryBuilder('mareeDate');
        $queryBuilder
                ->select('mareeDate')
                ->leftJoin('mareeDate.spot', 'spot')
                ->where("spot.id = :spotId")
                ->setParameter('spotId', $spot->getId())
                ->addOrderBy('mareeDate.datePrev', 'DESC')
                ->setMaxResults(1);
        try {
            return $queryBuilder->getQuery()->getOneOrNullResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }


    public function findLastPrev($number, $spot)
    {
        $queryBuilder = $this->createQueryBuilder('mareeDate');
        $queryBuilder
            ->select('mareeDate')
            ->leftJoin('mareeDate.spot', 'spot')
            ->where("spot.id = :spotId")
                ->setParameter('spotId', $spot->getId())
            ->addOrderBy('mareeDate.datePrev', 'ASC')
            ->setMaxResults($number);
        try {
            return $queryBuilder->getQuery()->getArrayResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

    // Return future marée from today
    // previsionMareeDateList
    public function getFuturMaree($spot)
    {
        $queryBuilder = $this->createQueryBuilder('mareeDate');
        $queryBuilder
            ->select('mareeDate')
            ->leftJoin('mareeDate.spot', 'spot')
            ->where("mareeDate.datePrev >= :datecourant and spot.id = :spotId")
                ->setParameters(array(
                    'datecourant'=> new \Datetime(date('d-m-Y')),
                    'spotId' => $spot->getId()
                ))
            ->addOrderBy('mareeDate.datePrev', 'ASC');
        //$queryBuilder->expr()->gte("mareeDate.datePrev", ":currentDate");
        //$queryBuilder->setParameter('currentDate', new \DateTime());

        try {
            return $queryBuilder->getQuery()->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }
}