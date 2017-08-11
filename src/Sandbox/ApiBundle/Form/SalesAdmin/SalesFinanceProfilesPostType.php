<?php

namespace Sandbox\ApiBundle\Form\SalesAdmin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SalesFinanceProfilesPostType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cover')
            ->add(
                'account',
                new SalesFinanceProfileAccountPostType()
            )
            ->add(
                'express',
                new SalesFinanceProfileExpressPostType()
            )
            ->add(
                'invoice',
                new SalesFinanceProfileInvoicePostType()
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyProfiles',
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
