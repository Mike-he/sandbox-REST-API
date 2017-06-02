<?php

namespace Sandbox\ApiBundle\Form\Order;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OrderReserveType extends AbstractType
{
    use HasOrderField;

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addOrderField($builder);

        $builder
            ->add(
                'time_unit',
                'text',
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
            'data_class' => 'Sandbox\ApiBundle\Entity\Order\ProductOrder',
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
