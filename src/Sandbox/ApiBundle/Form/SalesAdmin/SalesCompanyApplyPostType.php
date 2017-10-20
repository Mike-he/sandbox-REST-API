<?php

namespace Sandbox\ApiBundle\Form\SalesAdmin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SalesCompanyApplyPostType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('phone')
            ->add('website')
            ->add('contacter')
            ->add('contacter_phone')
            ->add('contacter_email')
            ->add('financial_contacter')
            ->add('financial_contacter_phone')
            ->add('financial_contacter_email')
            ->add('address')
            ->add('description')
            ->add('room_types')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyApply',
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
