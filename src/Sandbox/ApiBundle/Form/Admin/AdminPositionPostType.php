<?php

namespace Sandbox\ApiBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AdminPositionPostType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('parentPositionId')
            ->add('iconId')
            ->add('platform')
            ->add('salesCompanyId')
            ->add('permissions')
            ->add('currentPlatform')
            ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Admin\AdminPosition',
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
