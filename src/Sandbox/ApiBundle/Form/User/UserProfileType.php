<?php

namespace Sandbox\ApiBundle\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id')
            ->add('userid')
            ->add('companyid')
            ->add('name')
            ->add('email')
            ->add('phone')
            ->add('location')
            ->add('gender')
            ->add('aboutme')
            ->add('hobbies')
            ->add('skills')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\UserProfile',
        ));
    }

    public function getName()
    {
        return '';
    }
}
