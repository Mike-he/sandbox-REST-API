<?php

namespace Sandbox\ApiBundle\Form\Food;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FoodOrderType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('city_id', 'integer')
            ->add('building_id', 'integer')
            ->add('total_price')
            ->add('channel', 'text')
            ->add(
                'items',
                null,
                array(
                    'mapped' => false,
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
            'data_class' => 'Sandbox\ApiBundle\Entity\Food\FoodOrderPost',
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
