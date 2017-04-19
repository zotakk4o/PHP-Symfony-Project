<?php

namespace VehturiinikShopBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class DiscountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('discount',NumberType::class,['constraints'=>[new Range(['min' => '1','max' => '99']), new NotBlank(['message' => 'Cannot Be Blank!'])]])
            ->add('dateDiscountExpires',DateType::class,['years' => range(2017,2020),'constraints'=>[new Range(['min'=>'+1 day','max'=>'+3 years +11 months'])]])
            ->add('submit',SubmitType::class,['label'=>'Discount','attr'=>['class'=>'btn btn-primary']]);

    }
}
