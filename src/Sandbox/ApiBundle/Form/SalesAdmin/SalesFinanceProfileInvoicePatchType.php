<?php

namespace Sandbox\ApiBundle\Form\SalesAdmin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SalesFinanceProfileInvoicePatchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('category')
            ->add('taxpayer_id')
            ->add('address')
            ->add('phone')
            ->add('bank_account_name')
            ->add('bank_account_number');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyProfileInvoice',
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
