<?php

namespace Sandbox\ApiBundle\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('sex')
            ->add('phone')
            ->add('email')
            ->add('nationality')
            ->add('idType')
            ->add('idNumber')
            ->add('language')
            ->add('birthday')
            ->add('companyName')
            ->add('position')
            ->add('comment')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\User\UserInfo',
        ));
    }

    public function getName()
    {
        return '';
    }
}
