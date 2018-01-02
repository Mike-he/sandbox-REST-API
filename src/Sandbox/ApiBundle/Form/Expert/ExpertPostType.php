<?php

namespace Sandbox\ApiBundle\Form\Expert;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ExpertPostType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('gender')
            ->add('credential_no')
            ->add('phone')
            ->add('email')
            ->add('country_id')
            ->add('province_id')
            ->add('city_id')
            ->add('district_id')
            ->add('photo')
            ->add('identity')
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
