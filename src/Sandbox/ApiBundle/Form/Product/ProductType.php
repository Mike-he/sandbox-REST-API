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
            ->add('room_id', 'integer')
            ->add('description', 'text')
            ->add('visible_user_id', 'integer')
            ->add('base_price', 'money')
            ->add('unit_price')
            ->add('private')
            ->add('renewable')
            ->add('visible')
            ->add('start_date', 'date', array(
                    'widget' => 'single_text',
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
            ->add('earliest_rent_date', 'date', array(
                    'widget' => 'single_text',
                    'mapped' => false,
                )
            )
            ->add('deposit')
            ->add('rental_info')
            ->add('filename')
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
