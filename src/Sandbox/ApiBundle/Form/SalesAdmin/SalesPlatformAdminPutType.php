<?php

namespace Sandbox\ApiBundle\Form\SalesAdmin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SalesPlatformAdminPutType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password')
            ->add('name')
            ->add(
                'type_key',
                null,
                array(
                    'mapped' => false,
                )
            )
            ->add(
                'permission',
                null,
                array(
                    'mapped' => false,
                )
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdmin',
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
