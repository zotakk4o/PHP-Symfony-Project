<?php

namespace VehturiinikShopBundle\Form;

use function Sodium\add;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PurchaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userId', NumberType::class)
            ->add('productId', NumberType::class)
            ->add('quantityBought', NumberType::class)
            ->add('currentQuantity',NumberType::class)
            ->add('quantityForSale',NumberType::class)
            ->add('discount', NumberType::class)
            ->add('pricePerPiece', NumberType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => 'VehturiinikShopBundle\Entity\Purchase']);

    }

}
