<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;

class RegisterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name', TextType::class, array(
					'label' =>'Nombre',
					'required' => 'required',
					'attr' => array(
						'class' => 'form-name form-control')
				))
                ->add('surname', TextType::class, array(
					'label' =>'Apellidos',
					'required' => 'required',
					'attr' => array(
						'class' => 'form-surname form-control')
				))
                ->add('nickname', TextType::class, array(
					'label' =>'Nickname',
					'required' => 'required',
					'attr' => array(
						'class' => 'form-nickname form-control nickname-input')
				))
                ->add('email', EmailType::class, array(
					'label' =>'Correo electrónico',
					'required' => 'required',
					'attr' => array(
						'class' => 'form-email form-control')
				))
                /* ->add('password', PasswordType::class, array(
					'label' =>'Contraseña',
					'required' => 'required',
					'attr' => array(
						'class' => 'form-password form-control')
				)) */
				->add("password", RepeatedType::class, array(
					'type' => PasswordType::class,
					'invalid_message' => 'The password fields must match.',
					'options' => array('attr' => array("class"=>"form-password form-control")),
					'required' => true,
					'first_options'  => array('label' => 'Contraseña:'),
					'second_options' => array('label' => 'Confirma contraseña:')
				))
                ->add('Registrarse', SubmitType::class, array(
                    'attr'=>array(
						'class'=>'form-submit btn-register btn btn-success'
					)
				))
		;
    }
	
	/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'BackendBundle\Entity\User'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'backendbundle_user';
    }


}
