<?php

namespace VehturiinikShopBundle\Form;

use function Sodium\add;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', TextType::class, array('attr' => array('class' => 'form-control', 'placeholder' => 'Username')))
        ->add('password',PasswordType::class, array('attr' => array('class' => 'form-control', 'placeholder' => 'Password')))
        ->add('fullName', TextType::class, array('attr' => array('class' => 'form-control', 'placeholder' => 'Full Name')));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => 'VehturiinikShopBundle\Entity\User']);
    }


}
