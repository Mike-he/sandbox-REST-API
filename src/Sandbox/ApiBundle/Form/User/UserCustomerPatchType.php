<?php

namespace Sandbox\ApiBundle\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserCustomerPatchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('sex')
            ->add('email')
            ->add('nationality')
            ->add('id_type')
            ->add('id_number')
            ->add('language')
            ->add('birthday')
            ->add('company_name')
            ->add('position')
            ->add('comment')
            ->add('avatar')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\User\UserCustomer',
        ));
    }

    public function getName()
    {
        return '';
    }
}
