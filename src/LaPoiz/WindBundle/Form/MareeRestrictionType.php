<?php

namespace LaPoiz\WindBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MareeRestrictionType extends AbstractType
{
  public function getName()
  {
	return 'laPoiz_windBundle_mareeRestriction';
  }
  
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
      $builder->add('hauteurMax',IntegerType::class);
      $builder->add('hauteurMin',IntegerType::class);
      /*$builder->add('state','text');*/
      $builder->add('state',ChoiceType::class, array(
              'choices' => array('OK'=>'OK', 'warn'=>'warn', 'KO'=>'KO')
          )
      );
  }

  public function configureOptions(OptionsResolver $resolver)
  {
  	$resolver->setDefaults(array(
  			'data_class'      => 'LaPoiz\WindBundle\Entity\MareeRestriction'
  		));
  }
 }