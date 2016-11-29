<?php

namespace Sandbox\ApiBundle\Form\Order;

use Symfony\Component\Form\FormBuilderInterface;

trait HasOrderField
{
    /**
     * @param FormBuilderInterface $builder     Builder to modify
     * @param string               $application
     */
    protected function addOrderField(FormBuilderInterface $builder)
    {
        $builder
            ->add('product_id')
            ->add('user_id')
            ->add('start_date')
            ->add('rent_period')
            ->add('seat_id');
    }
}
