<?php

namespace Sandbox\ApiBundle\Form\Lease;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LeaseType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('building_id')
            ->add('product_id')
            ->add('lessor_name')
            ->add('lessor_address')
            ->add('lessor_contact')
            ->add('lessor_phone')
            ->add('lessor_email')
            ->add('lessee_type')
            ->add('lessee_enterprise')
            ->add('lessee_customer')
            ->add('start_date')
            ->add('end_date')
            ->add('monthly_rent')
            ->add('total_rent')
            ->add('deposit')
            ->add('purpose')
            ->add('other_expenses')
            ->add('supplementary_terms')
            ->add('status')
            ->add('is_auto')
            ->add('plan_day')
            ->add('lease_clue_id')
            ->add('lease_offer_id')
            ->add('bills',
                null,
                array(
                    'mapped' => false,
                )
            )
            ->add('lease_rent_types',
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
            'data_class' => 'Sandbox\ApiBundle\Entity\Lease\Lease',
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
