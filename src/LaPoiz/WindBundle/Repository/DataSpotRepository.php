<?php

namespace LaPoiz\WindBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * DataSpotRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DataSpotRepository extends EntityRepository
{

	public function findFromDate($date)
	{
		$queryBuilder = $this->createQueryBuilder('dataSpot');
		$queryBuilder->where($queryBuilder->expr()
				->gte('dataSpot.date',"'".$date." 00:00:00'"))
				->orderBy('dataSpot.date','ASC')
				->setMaxResults(1);;
		try {
			return $queryBuilder->getQuery()->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}
}