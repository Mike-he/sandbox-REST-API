<?php

namespace Sandbox\ApiBundle\Form\Food;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FoodItemType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('food_id', 'integer')
            ->add(
                'quantity',
                'integer',
                array(
                    'required' => false,
                )
            )
            ->add(
                'options',
                null,
                array(
                    'mapped' => false,
                )
            )
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Food\FoodItem',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return '';
    }
}
