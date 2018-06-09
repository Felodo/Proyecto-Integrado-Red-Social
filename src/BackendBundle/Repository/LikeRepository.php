<?php

namespace BackendBundle\Repository;

use Doctrine\ORM\EntityRepository;

use BackendBundle\Entity\Like;

class LikeRepository extends \Doctrine\ORM\EntityRepository{
	
	public function getLike($user_id){
		$em = $this->getEntityManager();
		
		$dql = "SELECT l FROM BackendBundle:Like l WHERE l.user = :user_id ORDER BY l.id DESC";
		
		$query = $em -> createQuery($dql)->setParameter('user_id', $user_id);
		
		return $query;
	}
	
}