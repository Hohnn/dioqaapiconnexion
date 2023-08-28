<?php

namespace Dioqaapiconnexion\Forms;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class DemoType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id_product_comment', HiddenType::class)
            ->add('id_product', NumberType::class, array(
                "attr" => array(
                    "placeholder" => "The product"
                )
            ))
            ->add('customer_name', TextType::class, array(
                "attr" => array(
                    "placeholder" => "The customer name"
                )
            ))
            ->add('title', TextType::class, array(
                "attr" => array(
                    "placeholder" => "The title"
                )
            ))
            ->add('content', TextType::class, array(
                "attr" => array(
                    "placeholder" => "The content"
                )
            ))
            ->add('grade', NumberType::class, array(
                "attr" => array(
                    "placeholder" => "The grade"
                )
            ))
            ->add('save', SubmitType::class);
    }
}
