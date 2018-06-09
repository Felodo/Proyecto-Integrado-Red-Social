<?php

namespace BackendBundle\Repository;

use Doctrine\ORM\EntityRepository;

class UserRepository extends \Doctrine\ORM\EntityRepository{
	
	public function getUsers_list(){
		$em = $this->getEntityManager();
		
		$query = $em->createQuery('SELECT u FROM BackendBundle:USER u');
		
		return $query;
	}
	
	public function getUsers($email, $nickname){
		$em = $this->getEntityManager();
		
		$query = $em->createQuery('SELECT u FROM BackendBundle:USER u WHERE u.email = :email OR u.nickname = :nickname')
					        ->setParameter('email', $email)
							->setParameter('nickname', $nickname);
		return $query;
	}
	
	public function search_user($search){
		$em = $this->getEntityManager();
		
		$dql = "SELECT u FROM BackendBundle:User u 
				WHERE u.name LIKE :search 
				OR u.surname LIKE :search 
				OR u.nickname LIKE :search 
				ORDER BY u.id ASC";
		$query = $em->createQuery($dql)->setParameter('search', "%$search%");
		
		return $query;
	}
	
	public function getFollowingUsers($user){
		$em = $this->getEntityManager();
		$following_repo = $em -> getRepository('BackendBundle:Following');
		$following = $following_repo->findBy(array('user' => $user));
		
		$following_array = array();
		foreach($following as $follow){
			$following_array[] = $follow->getFollowed();
		}
		
		$user_repo = $em -> getRepository('BackendBundle:User');
		$users = $user_repo -> createQueryBuilder('u')
			->where("u.id != :user AND u.id IN (:following)")
			->setParameter('user', $user->getId())
			->setParameter('following', $following_array)
			->orderBy('u.id', 'DESC');
			
		return $users;
	}
}