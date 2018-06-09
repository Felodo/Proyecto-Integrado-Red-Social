<?php
namespace AppBundle\Twig;

use Symfony\Bridge\Doctrine\RegistryInterface;

class ActiveExtension extends \Twig_Extension{
	protected $doctrine;
	
	public function __construct(RegistryInterface $doctrine){
		$this->doctrine = $doctrine;
	}
	
	public function getFilters(){
		return array(
			new \Twig_SimpleFilter('actived', array($this, 'activeFilters'))
		);
	}
	
	public function activeFilters($actived){
		$following_repo = $this->doctrine->getRepository('BackendBundle:User');
		$user_actived = $following_repo -> findOneBy(array(
			"id" => $actived,
			"active" => 1
		));
		
		if(!empty($user_actived) && is_object($user_actived)){
			$result = true;
		}else{
			$result = false;
		}
		
		return $result;
	}
	
	
	public function getName(){
		return 'active_extension';
	}
	
}