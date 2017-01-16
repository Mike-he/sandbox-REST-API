<?php

namespace Sandbox\ApiBundle\Form\Product;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProductPatchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isAnnualRent')
            ->add('annual_rent_unit_price')
            ->add('annual_rent_unit')
            ->add('annual_rent_description')
            ->add('visible');
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
