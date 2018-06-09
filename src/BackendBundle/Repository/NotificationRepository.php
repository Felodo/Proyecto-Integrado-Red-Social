<?php

namespace BackendBundle\Repository;

use Doctrine\ORM\EntityRepository;

use BackendBundle\Entity\Notification;

class NotificationRepository extends \Doctrine\ORM\EntityRepository{
	
	public function getNotification($user_id){
		$em = $this->getEntityManager();
		
		$dql = "SELECT n FROM BackendBundle:Notification n WHERE n.user = :user ORDER BY n.id DESC";
		
		$query = $em -> createQuery($dql)->setParameter('user', $user_id);
		
		return $query;
	}
	
}