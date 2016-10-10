<?php

namespace Sandbox\ApiBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AdminPostCheckPermissionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('permissions')
            ->add('op_level')
            ->add('platform')
            ->add('sales_company_id')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\AdminApiBundle\Data\Admin\AdminCheckPermission',
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
