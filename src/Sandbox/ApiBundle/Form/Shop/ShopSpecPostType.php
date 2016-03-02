<?php

namespace Sandbox\ApiBundle\Form\Shop;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ShopSpecPostType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'multiple',
                null,
                array(
                    'required' => false,
                )
            )
            ->add('name')
            ->add('inventory')
            ->add(
                'description',
                'text',
                array(
                    'required' => false,
                ))
            ->add('items');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Shop\ShopSpec',
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
