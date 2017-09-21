<?php

namespace Sandbox\ApiBundle\Form\Product;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProductType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('room_id')
            ->add('description')
            ->add('visible_user_id')
            ->add('private')
            ->add('renewable')
            ->add('visible')
            ->add('start_date',
                null,
                array(
                    'mapped' => false,
                )
            )
            ->add(
                'price_rule_include_ids',
                null,
                array(
                    'mapped' => false,
                )
            )
            ->add(
                'price_rule_exclude_ids',
                null,
                array(
                    'mapped' => false,
                )
            )
            ->add('seats')
            ->add('leasing_sets')
            ->add('rent_set')
            ->add(
                'rent_type_include_ids',
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
            'data_class' => 'Sandbox\ApiBundle\Entity\product\Product',
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
