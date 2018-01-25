<?php

namespace LaPoiz\WindBundle\Form;

use Genemu\Bundle\FormBundle\Form\Core\Type\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
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

    $builder->add('nbKmFromParis',NumberType::class,array('label'=>'Nombre de Km depuis Paris'));
    $builder->add('nbKmFromParisAutoroute',NumberType::class,array('label'=>'Nombre de Km sur Autoroute depuis Paris'));
    $builder->add('PayageFromParis',MoneyType::class,array('label'=>'Prix du payage depuis Paris'));
    $builder->add('nbMinuteFromParis',NumberType::class,array('label'=>'Durée (en minute) du trajet depuis Paris'));

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