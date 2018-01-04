<?php

namespace Sandbox\ApiBundle\Form\SalesAdmin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SalesCompanyPostType extends AbstractType
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
            ->add('address')
            ->add('contacter')
            ->add('contacter_phone')
            ->add('contacter_email')
            ->add('website')
            ->add('financial_contacter')
            ->add('financial_contacter_phone')
            ->add('financial_contacter_email')
            ->add('onlineSales')
            ->add('description')
            ->add('admins')
            ->add('coffee_admins')
            ->add('services')
            ->add('exclude_permissions')
            ->add('application_id')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany',
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
