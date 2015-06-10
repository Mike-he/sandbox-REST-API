<?php

namespace Sandbox\ApiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PasswordForgetVerifyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('token')
            ->add('code')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\IncomingData\PasswordForgetVerify',
        ));
    }

    public function getName()
    {
        return 'Sandbox_apibundle_passwordForgetVerify';
    }
}
