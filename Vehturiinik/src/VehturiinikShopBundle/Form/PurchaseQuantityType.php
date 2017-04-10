<?php

namespace VehturiinikShopBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PurchaseQuantityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('quantityForSale',NumberType::class,['attr' => ['class' => 'quantity-field']])
            ->add('productId', HiddenType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => 'VehturiinikShopBundle\Entity\Purchase']);
    }

}
