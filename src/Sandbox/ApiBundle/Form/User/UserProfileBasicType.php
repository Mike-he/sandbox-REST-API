<?php

namespace Sandbox\ApiBundle\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

class UserProfileBasicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('job_title')
            ->add('gender')
            ->add(
                $builder->create('date_of_birth', 'hidden')
                    ->addViewTransformer(new DateTimeToStringTransformer())
            )
            ->add('email')
            ->add('phone')
            ->add('about_me')
            ->add('skill')
            ->add('sina_weibo')
            ->add('tencent_weibo')
            ->add('facebook')
            ->add('linkedin')
            ->add('building_id')
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
