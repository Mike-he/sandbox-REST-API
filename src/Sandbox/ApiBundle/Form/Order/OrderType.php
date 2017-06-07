<?php

namespace Sandbox\ApiBundle\Form\Order;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OrderType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'product_id',
                'integer'
            )
            ->add('start_date')
            ->add(
                'rent_period',
                null,
                array(
                    'mapped' => false,
                )
            )
            ->add(
                'time_unit',
                'text',
                array(
                    'mapped' => false,
                )
            )
            ->add('price')
            ->add('rule_id')
            ->add('discount_price')
            ->add('isRenew')
            ->add('seat_id')
            ->add('rejected');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Order\ProductOrder',
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
