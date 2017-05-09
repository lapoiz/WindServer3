<?php

namespace LaPoiz\WindBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MareeType extends AbstractType
{
  public function getName()
  {
	return 'laPoiz_windBundle_maree';
  }
  
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
      $builder->add('mareeURL', TextType::class, array('label' => 'ULR pour la marée (http://maree.info/)'));
      $builder->add('hauteurMBGrandeMaree',HiddenType::class);
      $builder->add('hauteurMHGrandeMaree',HiddenType::class);
      $builder->add('hauteurMBMoyenneMaree',HiddenType::class);
      $builder->add('hauteurMHMoyenneMaree',HiddenType::class);
      $builder->add('hauteurMBPetiteMaree',HiddenType::class);
      $builder->add('hauteurMHPetiteMaree',HiddenType::class);


    $builder->add('mareeRestriction', CollectionType::class, array(
            'entry_type' => MareeRestrictionType::class,
            'label' => 'Restriction',
            'allow_add' => true,
            'allow_delete' => true
            //'by_reference' => false,
        ));


  }

  public function setDefaultOptions(OptionsResolver $resolver)
  {
  	$resolver->setDefaults(array(
  			'data_class'      => 'LaPoiz\WindBundle\Entity\Spot',
            'csrf_protection' => false,
            'attr' => array('id' => 'maree_form')
  		));
  }

 }