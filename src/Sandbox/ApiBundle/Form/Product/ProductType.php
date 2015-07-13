<?php

namespace Sandbox\ApiBundle\Form\Product;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProductType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('room_id', 'integer')
            ->add('description', 'text')
            ->add('visible_user_id', 'integer')
            ->add('base_price', 'money')
            ->add('unit_price')
            ->add('private')
            ->add('renewable')
            ->add('start_date', 'date', array(
                    'widget' => 'single_text',
                )
            )
            ->add('end_date', 'date', array(
                    'widget' => 'single_text',
                )
            )
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\product\Product',
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