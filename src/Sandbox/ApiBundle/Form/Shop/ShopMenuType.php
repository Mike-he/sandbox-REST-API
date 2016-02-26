<?php

namespace Sandbox\ApiBundle\Form\Shop;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ShopMenuType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'add',
                null,
                array(
                    'required' => false,
                )
            )
            ->add(
                'modify',
                null,
                array(
                    'required' => false,
                )
            )
            ->add(
                'remove',
                null,
                array(
                    'required' => false,
                )
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\AdminShopApiBundle\Data\Shop\ShopMenuData',
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
