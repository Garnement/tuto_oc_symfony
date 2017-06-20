<?php

namespace OC\PlatformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class AdvertType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('date', DateType::class)
                 ->add('title', TextType::class)
                 ->add('author', TextType::class, array('label'=>'Auteur'))
                 ->add('content', TextType::class)
                 ->add('image', ImageType::class)
                 ->add('categories', EntityType::class, array(
                     'class' => 'OCPlatformBundle:Category',
                     'choice_label' => 'name',
                     'multiple' => true,
                     'expanded' => true))
                 ->add('save', SubmitType::class);
        
        // On ajoute une fonction pour écouter l'évenement
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA, //1er argument : l'évenement, ici pre_set_data
            
            function(FormEvent $event) {
                //2eme argument, la fonction a executé

                //On récupère nore objet Advert sous jacent
                $advert = $event->getData();

                if(null === $advert)
                {
                    return;
                }
                // Si l'annonce n'est pas publiée, ou si elle n'existe pas encore en base (id est null)
                if(!$advert->getPusblished() || null === $advert->getId())
                {
                    // Alors on ajoute le champ published
                    $event->getForm()->add('published', CheckboxType::class, array('required'  => false));
                } else {
                    // Sinon, on le supprime
                    $event->getForm()->remove('published');
                }
                });
            }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'OC\PlatformBundle\Entity\Advert'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oc_platformbundle_advert';
    }


}
