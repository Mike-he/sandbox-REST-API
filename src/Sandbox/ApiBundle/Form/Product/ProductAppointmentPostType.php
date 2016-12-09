<?php

namespace Sandbox\ApiBundle\Form\Product;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProductAppointmentPostType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product_id')
            ->add('profile_id')
            ->add('rent_type')
            ->add('start_rent_date')
            ->add('rent_time_length');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Product\ProductAppointment',
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
