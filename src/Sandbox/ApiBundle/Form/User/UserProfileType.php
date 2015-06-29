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
            ->add('user_id')
            ->add('name')
            ->add('job_title')
            ->add('gender')
            ->add('date_of_birth')
            ->add('email')
            ->add('phone')
            ->add('aboutMe')
            ->add('skill')
            ->add('sina_weibo')
            ->add('tencent_weibo')
            ->add('facebook')
            ->add('linkedin')
            ->add('hobbies')
            ->add('experiences')
            ->add('educations')
            ->add('portfolios')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\User\UserProfile',
        ));
    }

    public function getName()
    {
        return '';
    }
}
