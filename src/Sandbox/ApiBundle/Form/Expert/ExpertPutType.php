<?php

namespace Sandbox\ApiBundle\Form\Expert;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ExpertPutType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('is_service')
            ->add('base_price')
            ->add('country_id')
            ->add('province_id')
            ->add('city_id')
            ->add('district_id')
            ->add('photo')
            ->add('identity')
            ->add('introduction')
            ->add('description')
            ->add(
                'field_ids',
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
            'data_class' => 'Sandbox\ApiBundle\Entity\Expert\Expert',
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
