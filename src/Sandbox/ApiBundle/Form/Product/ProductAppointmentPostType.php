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
            ->add('applicant_name')
            ->add('applicant_company')
            ->add('applicant_phone')
            ->add('applicant_email')
            ->add('start_rent_date')
            ->add('rent_time_length')
            ->add('rent_time_unit');
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
