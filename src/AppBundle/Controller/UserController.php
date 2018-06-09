<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use BackendBundle\Entity\Following;
use BackendBundle\Entity\Like;
use BackendBundle\Entity\Notification;
use BackendBundle\Entity\Message_Private;
use BackendBundle\Entity\Publication;
use BackendBundle\Entity\User;
use BackendBundle\Repository\UserRepository;
use AppBundle\Form\RegisterType;
use AppBundle\Form\UserType;

class UserController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    /*public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ]);
    }*/
	
	private $session;
	
	public function __construct(){
		$this->session = new Session();
	}
	
	public function loginAction(Request $request){
		if(is_object($this->getUser()) && $this->getUser()->getActive() == 1){
			return $this->redirect('home');
		}else if(is_object($this->getUser()) && $this->getUser()->getActive() == 0){
			return $this->redirect('logout');
		}

		$authenticationUtils = $this->get('security.authentication_Utils');
		$error = $authenticationUtils->getLastAuthenticationError();
		$lastUsername = $authenticationUtils->getLastUsername();
		
		return $this->render('AppBundle:User:login.html.twig', array(
			'last_username' => $lastUsername,
			'error' => $error
		)); 
	}
	
	public function registerAction(Request $request){
		if(is_object($this->getUser())){
			return $this->redirect('home');
		}
		
		$user = new User();
		$form = $this->createForm(RegisterType::class, $user);
		
		$form->handleRequest($request);
		if($form->isSubmitted()){
			if($form->isValid()){
				$em = $this->getDoctrine()->getManager();
				$user_repo = $em->getRepository("BackendBundle:User");
				
				$query = $user_repo -> getUsers($form->get("email")->getData(), $form->get("nickname")->getData());
				/*$query = $em->createQuery('SELECT u FROM BackendBundle:USER u WHERE u.email = :email OR u.nickname = :nickname')
					        ->setParameter('email', $form->get("email")->getData())
							->setParameter('nickname', $form->get("nickname")->getData());*/
							
				$user_isset = $query->getResult();
				
				if(count($user_isset) == 0){
					$factory = $this->get("security.encoder_factory");
					$encoder = $factory->getEncoder($user);
					
					$password = $encoder->encodePassword($form->get("password")->getData(),$user->getSalt());
					
					$user->setPassword($password);
					$user->setActive(0);
					$user->setRole("ROLE_USER");
					$user->setImage(null);
					
					$em->persist($user);
					$flush=$em->flush();
				
					if($flush == null){
						$status = "Te has registrado correctemente";
						$this->session->getFlashBag()->add("status", $status);
						
						return $this->redirect("login");
					}else{
						$status = "No te has registrado correctamente";
					}
				}else{
					$status = "El usuario ya existe";
				}
				
				
				
			}else{
				$status= "No se ha registrado correctamente";
			}
			//var_dump($status);
			$this->session->getFlashBag()->add("status", $status);
		}
		
		return $this->render('AppBundle:User:register.html.twig', array(
			"form" => $form->createView()
		));
	}
	
	/*COMPROBAR LA EXISTENCIA DE NICKNAME*/
	public function nicknameTestAction(Request $request){
		$nickname = $request->get("nickname");
		
		$em = $this->getDoctrine()->getManager();
		$user_repo = $em->getRepository("BackendBundle:User");
		
		$user_isset = $user_repo->findOneBy(array("nickname" => $nickname));
		
		$result = "used";
		if(count($user_isset) >= 1 && is_object($user_isset)){
			$result = "used";
		}else{
			$result = "unused";
		}
		
		return new Response($result);
	}
	
	/*EDITAR PERFIL DE USUSARIO*/
	public function editUserAction(Request $request){
		$user = $this->getUser();
		$user_image = $user->getImage();
		$form = $this->createForm(UserType::class, $user);
		
		$form->handleRequest($request);
		if($form->isSubmitted()){
			if($form->isValid()){
				$em = $this->getDoctrine()->getManager();
				
				$user_repo = $em->getRepository("BackendBundle:User");
				
				$query = $user_repo -> getUsers($form->get("email")->getData(), $form->get("nickname")->getData());
				
				
				//$query = $em -> getPublications($form->get("email")->getData(), $form->get("nickname")->getData());
				//$user_repo = $em->getRepository("BackendBundle:User");
				/*$query = $em->createQuery('SELECT u FROM BackendBundle:USER u WHERE u.email = :email OR u.nickname = :nickname')
					        ->setParameter('email', $form->get("email")->getData())
							->setParameter('nickname', $form->get("nickname")->getData());*/
							
				$user_isset = $query->getResult();
				
				if(count($user_isset) == 0 || ($user->getEmail() == $user_isset[0]->getEmail() || $user->getNickname()== $user_isset[0]->getNickname())){
					
					$file = $form['image']->getData();
					if(!empty($file) && $file != null){
						$ext = $file->guessExtension();
						
						if($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif'){
							$file_name = $user->getId().time().'.'.$ext;
							$file->move('uploads/users', $file_name);
							
							$user ->setImage($file_name);
						}
						
					}else{
						$user ->setImage($user_image);
					}
					
					
					
					
					/* $factory = $this->get("security.encoder_factory");
					$encoder = $factory->getEncoder($user);
					
					$password = $encoder->encodePassword($form->get("password")->getData(),$user->getSalt());
					
					$user->setPassword($password);
					$user->setRole("ROLE_USER"); */
					//$user->setImage(null);
					
					$em->persist($user);
					$flush=$em->flush();
				
					if($flush == null){
						$status = "Has modificado tus datos correctemente";
						//$this->session->getFlashBag()->add("status", $status);
						
						//return $this->redirect("login");
					}else{
						$status = "No has actualizado tus datos correctamente";
					}
				}else{
					$status = "El usuario ya existe";
				}
				
				
				
			}else{
				$status= "No has actualizado tus datos correctamente";
			}
			//var_dump($status);
			$this->session->getFlashBag()->add("status", $status);
			return $this->redirect('my-data');
		}
		
		//echo "Accion editar datos";
		//die();
		return $this->render('AppBundle:User:edit_user.html.twig', array(
		    "form" => $form->createView()
		));
	}
	
	public function listUsersAction(Request $request){
		$em = $this->getDoctrine()->getManager();
		
		$user_repo = $em->getRepository("BackendBundle:User");
				
		$query = $user_repo -> getUsers_list();
		
		
		/*$dql = "SELECT u FROM BackendBundle:User u";
		$query = $em->createQuery($dql);*/
		
		$paginator = $this->get('knp_paginator');
		$pagination = $paginator -> paginate(
										$query, 
										$request->query->getInt('page',1), 
										5
									);
		
		return $this->render('AppBundle:User:list_users.html.twig', array(
			'pagination' => $pagination
		));
		
		
		
		/* var_dump("List users action");
		die(); */
	}
	
	public function searchUserAction(Request $request){
		$em = $this->getDoctrine()->getManager();
		
		$search = trim($request->query->get("search", null));
		
		if($search == null){
			return $this->redirect($this->generateURL('home_publications'));
		}
		
		$user_repo = $em->getRepository("BackendBundle:User");
		$query = $user_repo -> search_user($search);
		/* $dql = "SELECT u FROM BackendBundle:User u 
				WHERE u.name LIKE :search 
				OR u.surname LIKE :search 
				OR u.nickname LIKE :search 
				ORDER BY u.id ASC";
		$query = $em->createQuery($dql)->setParameter('search', "%$search%"); */
		
		$paginator = $this->get('knp_paginator');
		$pagination = $paginator -> paginate(
										$query, 
										$request->query->getInt('page',1), 
										5
									);
		
		return $this->render('AppBundle:User:list_users.html.twig', array(
			'pagination' => $pagination
		));
	}
	
	public function profileAction(Request $request, $nickname = null){
		$em = $this-> getDoctrine() -> getManager();
		
		if($nickname != null){
			$user_repo = $em->getRepository('BackendBundle:User');
			$user = $user_repo -> findOneBy(array('nickname' => $nickname));
		}else{
			$user = $this->getUser();
		}
		
		if(empty($user) || !is_object($user)){
			return $this->redirect($this->generateURL('home_publications'));
		}
		
		$user_id = $user->getId();
		$dql = "SELECT p FROM BackendBundle:Publication p WHERE p.user = $user_id ORDER BY p.id DESC";
		
		$query = $em -> createQuery($dql);
		
		$paginator = $this->get('knp_paginator');
		$publications = $paginator -> paginate(
			$query,
			$request->query->getInt('page', 1),
			5
		);
		
		return $this->render('AppBundle:User:profile.html.twig', array(
			'user' => $user,
			'pagination' => $publications
		));
	}
	
	public function activeAction(Request $request){
		$actived = $request -> get('actived');
		
		$em = $this->getDoctrine() -> getManager();
		
		$user_repo = $em -> getRepository('BackendBundle:User');
		
		$user_active = $user_repo -> find($actived);
		
		$user_active -> setActive(1);
		
		$em -> persist($user_active);
		
		$flush = $em -> flush();
		
		if($flush == null)
			$status = "Usuario activo";
		else
			$status = "No se ha podido activar este usuario";
		
		return new Response($status);
	}
	
	public function deactiveAction(Request $request){
		$actived = $request -> get('actived');
		
		$em = $this->getDoctrine() -> getManager();
		
		$user_repo = $em -> getRepository('BackendBundle:User');
		
		$user_active = $user_repo -> find($actived);
		
		$user_active -> setActive(0);
		
		$em -> persist($user_active);
		
		$flush = $em -> flush();
		
		if($flush == null)
			$status = "Usuario no activo";
		else
			$status = "No se ha podido desactivar este usuario";
		
		return new Response($status);
	}
	
	public function deleteAction(Request $request){
		$user = $request -> get('user');
		
		$em = $this->getDoctrine() -> getManager();
		
		$user_repo = $em -> getRepository('BackendBundle:User');
		
		$publication_repo = $em->getRepository("BackendBundle:Publication");
        $publication = $publication_repo->findBy(['user' => $user]);
		
		$private_message_repo = $em->getRepository("BackendBundle:PrivateMessage");
        $private_message_emitter = $private_message_repo->findBy(['emitter' => $user]);
		$private_message_receiver = $private_message_repo->findBy(['receiver' => $user]);
		
		$notification_repo = $em->getRepository("BackendBundle:Notification");
        $notification = $notification_repo->findBy(['user' => $user]);
		
		$like_repo = $em->getRepository("BackendBundle:Like");
        $like = $notification_repo->findBy(['user' => $user]);
		
		$following_repo = $em->getRepository("BackendBundle:Following");
        $following = $following_repo->findBy(['user' => $user]);
		$followed = $following_repo->findBy(['followed' => $user]);
		
		$user_active = $user_repo -> find($user);
		
		if (count($publication) == 0 && count($private_message_emitter) == 0 && count($private_message_receiver) == 0 && count($like) == 0 && count($following) == 0 && count($followed) == 0) 
		{
			$em->remove($user_active);
			$flush = $em->flush();
		
			if($flush == null)
				$status = "Usuario no activo";
			else
				$status = "No se ha podido desactivar este usuario";
		}else{
			$status = "Error: El usuario no se pudo eliminar correctamente!!";
		}
		
		$query = $user_repo -> getUsers_list();
		
		$paginator = $this->get('knp_paginator');
		$pagination = $paginator -> paginate(
										$query, 
										$request->query->getInt('page',1), 
										5
									);
		
		return $this->render('AppBundle:User:list_users.html.twig', array(
			'pagination' => $pagination
		));
		
		$this->session->getFlashBag()->add("status", $status);
		return new Response($status);
	}
	
	
	
}
