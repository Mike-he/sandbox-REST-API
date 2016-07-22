<?php

namespace Sandbox\ApiBundle\Form\SalesAdmin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SalesAdminPutType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username')
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
                'company',
                null,
                array(
                    'mapped' => false,
                )
            )
            ->add('banned')
            ->add(
                'exclude_permissions',
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
