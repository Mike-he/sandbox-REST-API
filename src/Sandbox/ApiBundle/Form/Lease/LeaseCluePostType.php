<?php

namespace Sandbox\ApiBundle\Form\Lease;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LeaseCluePostType extends AbstractType
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
            ->add('lessee_name')
            ->add('lessee_address')
            ->add('lessee_customer')
            ->add('lessee_phone')
            ->add('lessee_email')
            ->add('start_date')
            ->add('end_date')
            ->add('cycle')
            ->add('monthly_rent')
            ->add('number')
            ->add('status')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Lease\LeaseClue',
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
