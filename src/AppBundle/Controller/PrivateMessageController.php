<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use BackendBundle\Entity\User;
use BackendBundle\Entity\PrivateMessage;
use BackendBundle\Repository\PrivateMessageRepository;
use AppBundle\Form\PrivateMessageType;

class PrivateMessageController extends Controller
{
	
	private $session;
	
	public function __construct(){
		$this->session = new Session();
	}
	
	public function indexAction(Request $request){
		$em = $this->getDoctrine()->getManager();
		
		$user = $this->getUser();
		
		$private_message = new PrivateMessage();
		$form = $this->createForm(PrivateMessageType::class, $private_message, array(
			'empty_data' => $user
		));
		
		$form -> handleRequest($request);
		
		if($form->isSubmitted()){
			if($form->isValid()){
				$file = $form['image']->getData();
				if(!empty($file) && $file!= null){
					$ext = $file->guessExtension();
					if($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif')
					{
						$file_name = $user->getId().time().".".$ext;
						$file->move('uploads/messages/images', $file_name);
						
						$private_message->setImage($file_name);
					}else{
						$private_message ->setImage(null);
					}
				}else{
					$private_message ->setImage(null);
				}
				
				$file_document = $form['file']->getData();
				if(!empty($file_document) && $file_document!= null){
					$ext = $file_document->guessExtension();
					if($ext == 'pdf' || $ext == 'doc' || $ext == 'docx' || $ext == 'txt')
					{
						$file_name_document = $user->getId().time().".".$ext;
						$file_document->move('uploads/messages/files', $file_name_document);
						
						$private_message->setFile($file_name_document);
					}else{
						$private_message ->setFile(null);
					}
				}else{
					$private_message ->setFile(null);
				}
				
				$private_message->setEmitter($user);
				$private_message->setCreatedAt(new \DateTime("now"));
				$private_message->setReaded(0);
				
				$em->persist($private_message);
				$flush=$em->flush();
				
				if($flush == null){
					$status = 'El mensaje se ha enviado correctamente';
				}else{
					$status = 'Error al enviar el mensaje';
				}
			}else{
				$status = "El mensaje privado no se ha enviado";
			}
			$this->session->getFlashBag()->add("status", $status);
			return $this->redirectToRoute("private_message_index");
		}
		
		$private_messages = $this->getPrivateMessages($request);
		
		$this->setReaded($em, $user);
		
		return $this->render('AppBundle:PrivateMessage:index.html.twig', array(
			'form' => $form->createView(),
			"titulo" => 'Mensaje privado',
			'pagination' => $private_messages
		));
	}
	
	/*public function denunceAction(Request $request, $publication_id){
		$em = $this->getDoctrine()->getManager();
		
		$user = $this->getUser();
		
		$private_message = new PrivateMessage();
		
		$private_message->setEmitter($user);
		$private_message->setReceiver($user);
		$private_message->setMessage("El usuario ".$user->getNickname()." ha denunciado la publicacion (".$publication_id.")");
		$private_message->setCreatedAt(new \DateTime("now"));
		$private_message->setReaded(0);
		
		$em->persist($private_message);
		$flush=$em->flush();
		
		return new Response($status);
	}*/
	
	public function sendedAction(Request $request){
		$private_messages = $this->getPrivateMessages($request, "sended");
		
		return  $this->render('AppBundle:PrivateMessage:sended.html.twig', array(
			'pagination' => $private_messages
		));
	}
	
	public function getPrivateMessages($request, $type = null)
	{
		$em = $this->getDoctrine()->getManager();
		
		$user = $this->getUser();
		$user_id = $user -> getId();
		
		
		$message_repo = $em->getRepository("BackendBundle:PrivateMessage");
		$query = $message_repo -> getPrivateMessage($user_id, $type);
		/* if($type == "sended"){
			$dql = "SELECT p FROM BackendBundle:PrivateMessage p WHERE p.emitter = $user_id ORDER BY p.id DESC";
			
		}else{
			$dql = "SELECT p FROM BackendBundle:PrivateMessage p WHERE p.receiver = $user_id ORDER BY p.id DESC";
		}
		 
		$query = $em -> createQuery($dql);*/
		
		$paginator = $this->get('knp_paginator');
		$pagination = $paginator -> paginate($query, $request->query->getInt('page',1), 5);
		
		return $pagination;
		
	}
	
	public function notReadedAction(){
		$em = $this->getDoctrine()->getManager();
		$user = $this->getUser();
		
		$private_message_repo = $em ->getRepository('BackendBundle:PrivateMessage');
		
		$count_not_readed = count($private_message_repo->findBy(array(
			'receiver' => $user,
			'readed' => 0
		)));
		
		return new Response($count_not_readed);
	}
	
	private function setReaded($em, $user){
		$private_message_repo = $em ->getRepository('BackendBundle:PrivateMessage');
		$messages = $private_message_repo->findBy(array(
			'receiver' => $user,
			'readed' => 0
		));
		
		foreach($messages as $msg){
			$msg -> setReaded(1);
			$em -> persist($msg);
		}
		$flush=$em->flush();
		
		if($flush == null){
			$result = true;
		}else{
			$result = false;
		}
		
		return $result;
	}
}
