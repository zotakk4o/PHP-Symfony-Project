<?php

namespace VehturiinikShopBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username',TextType::class,array('attr' => array('class' => 'form-control', 'placeholder' => 'Username')))
            ->add('fullName', TextType::class,array('attr' => array('class' => 'form-control', 'placeholder' => 'Full Name')))
            ->add('roles', ChoiceType::class, array(
                'multiple' => true,
                'choices' => array(
                    'ROLE_EDITOR' => 'ROLE_EDITOR',
                    'ROLE_ADMIN' => 'ROLE_ADMIN'
                ),
                'expanded' => true,
            ))
            ->add('submit', SubmitType::class, array('label' => 'Edit User','attr' => ['class' => 'btn btn-primary']));;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => 'VehturiinikShopBundle\Entity\User']);

    }


}
