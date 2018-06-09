<?php

namespace BackendBundle\Repository;

use Doctrine\ORM\EntityRepository;

use BackendBundle\Entity\PrivateMessage;

class PrivateMessageRepository extends \Doctrine\ORM\EntityRepository{
	
	public function getPrivateMessage($user_id, $type){
		$em = $this->getEntityManager();
		
		if($type == "sended"){
			$dql = "SELECT p FROM BackendBundle:PrivateMessage p WHERE p.emitter = :user ORDER BY p.id DESC";
			
		}else{
			$dql = "SELECT p FROM BackendBundle:PrivateMessage p WHERE p.receiver = :user ORDER BY p.id DESC";
		}
		
		$query = $em -> createQuery($dql)->setParameter('user', $user_id);
		
		
		
		return $query;
	}
	
}