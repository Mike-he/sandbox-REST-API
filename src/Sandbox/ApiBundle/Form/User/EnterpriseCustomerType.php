<?php

namespace Sandbox\ApiBundle\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EnterpriseCustomerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('register_address')
            ->add('business_license_number')
            ->add('organization_certificate_code')
            ->add('taxRegistration_number')
            ->add('taxpayer_identification_number')
            ->add('bank_name')
            ->add('bank_account_number')
            ->add('website')
            ->add('phone')
            ->add('industry')
            ->add('mailing_address')
            ->add('comment')
            ->add('contacts', null, array('required' => false))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\User\EnterpriseCustomer',
        ));
    }

    public function getName()
    {
        return '';
    }
}