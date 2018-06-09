<?php

namespace BackendBundle\Repository;

use Doctrine\ORM\EntityRepository;

use BackendBundle\Entity\Publication;
use BackendBundle\Entity\Like;

class PublicationRepository extends \Doctrine\ORM\EntityRepository{
	
	public function getPublications($user_id, $following_array){
		$em = $this->getEntityManager();
        
        $dql = "SELECT p FROM BackendBundle\Entity\Publication p WHERE p.user = :user_id OR p.user IN (:following) ORDER BY p.id DESC";
		
		$query = $em->createQuery($dql)
			->setParameter('user_id', $user_id)
			->setParameter('following', $following_array);
		//$publications_repo = $em->getRepository('BackendBundle:Publication');
		
		/*$query = $publications_repo -> createQueryBuilder('p')
					-> where('p.user = (:user_id) OR p.user IN (:following)')
					->setParameter('user_id', $user_id)
					->setParameter('following', $following_array)
					->orderBy('p.id', 'DESC')
					->getQuery();*/
		return $query;
	}
}