<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use BackendBundle\Repository\PublicationRepository;
use BackendBundle\Entity\Publication;
use AppBundle\Form\PublicationType;

class PublicationController extends Controller
{
	private $session;
	
	public function __construct(){
		$this->session = new Session();
	}
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
		$em=$this->getDoctrine()->getManager();
		$user = $this->getUser();
		$publication = new Publication();
		$form = $this->createForm(PublicationType::class, $publication);
		
		$form -> handleRequest($request);
		
		if($form->isSubmitted()){
			if($form->isValid()){
				
				$file = $form['image']->getData();
				if(!empty($file) && $file!= null){
					$ext = $file->guessExtension();
					if($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif')
					{
						$file_name = $user->getId().time().".".$ext;
						$file->move('uploads/publications/images', $file_name);
						
						$publication->setImage($file_name);
					}else{
						$publication ->setImage(null);
					}
				}else{
					$publication ->setImage(null);
				}
				
				$file_document = $form['document']->getData();
				if(!empty($file_document) && $file_document!= null){
					$ext = $file_document->guessExtension();
					if($ext == 'pdf' || $ext == 'doc' || $ext == 'docx' || $ext == 'txt')
					{
						$file_name_document = $user->getId().time().".".$ext;
						$file_document->move('uploads/publications/documents', $file_name_document);
						
						$publication->setDocument($file_name_document);
					}else{
						$publication ->setDocument(null);
					}
				}else{
					$publication ->setDocument(null);
				}
				
				$publication ->setUser($user);
				$publication ->setCreatedAt(new \DateTime("now"));
				
				$em->persist($publication);
				$flush=$em->flush();
				
				if($flush == null){
					$status = 'La publicacion se ha creado correctamente';
				}else{
					$status = 'Error al añadir la publicacion';
				}
			}
			else{
				$status = 'La publicación no se ha creado porque el formulario no es valido';
			}
			
			$this->session->getFlashBag()->add("status", $status);
			return $this->redirectToRoute('home_publications');
		}
		
		$publications = $this->getPublications($request);
		
		//$fecha = $this->getPublications($request)->
		
        // replace this example code with whatever you need
        return $this->render('AppBundle:Publication:home.html.twig', array(
			'form' => $form->createView(),
			'pagination' => $publications
			//'fecha' => $fecha
		));
    }
	
	public function getPublications($request){
		$em = $this->getDoctrine()->getManager();
		
		$user = $this->getUser();
		
		$publications_repo = $em->getRepository('BackendBundle:Publication');
		$following_repo = $em->getRepository('BackendBundle:Following');
		
		$following = $following_repo->findBy(array('user' => $user));
		
		$following_array = array();
		foreach($following as $follow){
			$following_array[] = $follow->getFollowed();
		}
		
		$query = $publications_repo -> getPublications($user->getId(), $following_array);
		
		/*$query = $publications_repo ->createQueryBuilder('p')
					-> where('p.user = (:user_id) OR p.user IN (:following)')
					->setParameter('user_id', $user->getId())
					->setParameter('following', $following_array)
					->orderBy('p.id', 'DESC')
					->getQuery();*/
		
		$paginator = $this->get('knp_paginator');
		$pagination = $paginator -> paginate(
					$query,
					$request->query->getInt('page', 1),
					5);
		
		return $pagination;
	}
	
	public function removePublicationAction(Request $request, $id = null){
		$em = $this->getDoctrine()->getManager();
		$publication_repo = $em->getRepository('BackendBundle:Publication');
		$publication = $publication_repo ->find($id);
		
		if($this->getUser()->getRole() == "ROLE_ADMIN")
		{
			$user = $publication->getUser();
		}else{
			$user = $this->getUser();
		}
		
		if($user->getId() == $publication->getUser()->getId()){
			$em->remove($publication);
			$flush = $em->flush();
			
			if($flush == null){
				$status = "La publicacion se ha borrado correctamente";
			}else{
				$status = "La publicación no se ha borrado";
			}
		}else{
			$status = "La publicación no se ha borrado";
		}
		
		return new Response($status);
	}
}
