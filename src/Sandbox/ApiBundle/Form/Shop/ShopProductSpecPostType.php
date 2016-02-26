<?php

namespace Sandbox\ApiBundle\Form\Shop;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ShopProductSpecPostType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('shop_spec_id')
            ->add(
                'optional',
                null,
                array(
                    'required' => false,
                )
            )
            ->add('items');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Shop\ShopProductSpec',
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
