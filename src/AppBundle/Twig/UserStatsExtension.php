<?php

namespace AppBundle\Twig;

use Symfony\Bridge\Doctrine\RegistryInterface;

class UserStatsExtension extends \Twig_Extension {
	protected $doctrine;
	
	public function __construct(RegistryInterface $doctrine){
		$this->doctrine = $doctrine;
	}
	
	public function getFilters(){
		return array(
			new \Twig_SimpleFilter('user_stats', array($this, 'userStatsFilter'))
		);
	}
	
	public function userStatsFilter($user){
		$following_repo = $this->doctrine->getRepository('BackendBundle:Following');
		$publication_repo = $this->doctrine->getRepository('BackendBundle:Publication');
		$like_repo = $this->doctrine->getRepository('BackendBundle:Like');
		
		$user_following = $following_repo->findBy(array('user' => $user));
		$user_followers = $following_repo->findBy(array('followed' => $user));
		$user_publication = $publication_repo->findBy(array('user' => $user));
		$user_like = $like_repo->findBy(array('user' => $user));
		
		$result = array(
			'following' => count($user_following),
			'followers' => count($user_followers),
			'publications' => count($user_publication),
			'likes' => count($user_like)
		);
		
		return $result;
	}
	
	public function getName(){
		return 'user_stats_extension';
	}
}
