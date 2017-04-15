<?php

namespace VehturiinikShopBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PurchaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userId', NumberType::class)
            ->add('productId', NumberType::class)
            ->add('quantity',NumberType::class)
            ->add('quantityForSale',NumberType::class)
            ->add('discount', NumberType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => 'VehturiinikShopBundle\Entity\Purchase']);

    }

}
