<?php

namespace LaPoiz\WindBundle\Form;

use Genemu\Bundle\FormBundle\Form\Core\Type\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpotType extends AbstractType
{
  public function getName()
  {
	return 'spot';
  }
  
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('nom');

    $builder->add('description',TinymceType::class);

    $builder->add('localisationDescription',TinymceType::class);

    $builder->add('gpsLong',NumberType::class,array('label'=>'GPS long'));
    $builder->add('gpsLat',NumberType::class,array('label'=>'GPS lat'));

    $builder->add('infoOrientation',TinymceType::class);
    $builder->add('infoMaree',TinymceType::class);

//    $builder->add('windOrientation', CollectionType::class, array('entry_type' => WindOrientationType::class, 'label'=>' '));
    //$builder->add('windOrientation', 'collection', array('type' => new OrientationWindType(), 'label'=>' '));

  }

  public function setDefaultOptions(OptionsResolver $resolver)
  {
  	$resolver->setDefaults(array(
  			'data_class'      => 'LaPoiz\WindBundle\Entity\Spot',
            'csrf_protection' => false,
            'attr' => array('id' => 'spot_form')
  		));
  }

 }