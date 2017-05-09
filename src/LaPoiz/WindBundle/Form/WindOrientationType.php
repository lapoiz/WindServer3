<?php

namespace LaPoiz\WindBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WindOrientationType extends AbstractType
{
  public function getName()
  {
	//return 'orientationWind';
      return 'windOrientation';
  }
  
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('orientation',HiddenType::class); // use in id of the input
    $builder->add('state',HiddenType::class, array('required' => false));
  }

  public function setDefaultOptions(OptionsResolver $resolver)
  {
  	$resolver->setDefaults(array(
  			'data_class'      => 'LaPoiz\WindBundle\Entity\WindOrientation'
  		));
  }
 }