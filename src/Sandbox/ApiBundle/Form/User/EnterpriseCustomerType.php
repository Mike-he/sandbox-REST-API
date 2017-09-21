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
            ->add('register_address', null, array('required' => false))
            ->add('business_license_number', null, array('required' => false))
            ->add('organization_certificate_code', null, array('required' => false))
            ->add('tax_registration_number', null, array('required' => false))
            ->add('taxpayer_identification_number', null, array('required' => false))
            ->add('bank_name', null, array('required' => false))
            ->add('bank_account_number', null, array('required' => false))
            ->add('website', null, array('required' => false))
            ->add('phone', null, array('required' => false))
            ->add('industry', null, array('required' => false))
            ->add('mailing_address', null, array('required' => false))
            ->add('comment', null, array('required' => false))
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
