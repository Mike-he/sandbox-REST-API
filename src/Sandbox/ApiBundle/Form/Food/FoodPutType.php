<?php

namespace Sandbox\ApiBundle\Form\Food;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FoodPutType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add(
                'price',
                null,
                array(
                    'required' => false,
                )
            )
            ->add(
                'inventory',
                'integer',
                array(
                    'required' => false,
                )
            )
            ->add(
                'food_attachments',
                null,
                array(
                    'mapped' => false,
                )
            )
            ->add(
                'food_forms',
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
            'data_class' => 'Sandbox\ApiBundle\Entity\Food\Food',
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
