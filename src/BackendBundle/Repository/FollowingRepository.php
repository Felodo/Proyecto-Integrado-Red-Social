<?php

namespace BackendBundle\Repository;

use Doctrine\ORM\EntityRepository;

use BackendBundle\Entity\Following;

class FollowingRepository extends \Doctrine\ORM\EntityRepository{
	
	public function getfollowing($user){
		$em = $this->getEntityManager();
		
		$dql = "SELECT f FROM BackendBundle:Following f WHERE f.user = :user ORDER BY f.id DESC";
		
		$query = $em -> createQuery($dql)->setParameter('user', $user);
		
		return $query;
	}
	
	public function getfollowed($user_id){
		$em = $this->getEntityManager();
		
		$dql = "SELECT f FROM BackendBundle:Following f WHERE f.followed = :user_id ORDER BY f.id DESC";
		
		$query = $em -> createQuery($dql) ->setParameter('followed', $user_id);
		
		return $query;
	}
}