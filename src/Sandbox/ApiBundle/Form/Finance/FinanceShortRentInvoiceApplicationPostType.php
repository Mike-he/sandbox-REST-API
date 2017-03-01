<?php

namespace Sandbox\ApiBundle\Form\Finance;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FinanceShortRentInvoiceApplicationPostType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('invoiceNo')
            ->add('invoiceIds')
            ->add('officialProfileId')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Finance\FinanceShortRentInvoiceApplication',
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
