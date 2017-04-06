<?php

namespace Sandbox\ApiBundle\Form\MembershipCard;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MembershipCardPostType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('background')
            ->add('description')
            ->add('instructions')
            ->add('phone')
            ->add('doors_control')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\MembershipCard\MembershipCard',
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
