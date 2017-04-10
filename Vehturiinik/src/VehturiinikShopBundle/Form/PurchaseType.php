<?php

namespace VehturiinikShopBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PurchaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('userId',NumberType::class,array('label' => 'User Id','attr' => array('class' => 'form-control')))
            ->add('productId',NumberType::class,array('label' => 'Product Id','attr' => array('class' => 'form-control')))
            ->add('quantity',NumberType::class,array('label' => 'Quantity','attr' => array('class' => 'form-control')))
            ->add('quantityForSale',NumberType::class,array('label' => 'Quantity For Sale','attr' => array('class' => 'form-control')));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => 'VehturiinikShopBundle\Entity\Purchase']);

    }

}
