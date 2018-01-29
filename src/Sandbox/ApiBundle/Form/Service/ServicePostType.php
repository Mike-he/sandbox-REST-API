<?php

namespace Sandbox\ApiBundle\Form\Service;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ServicePostType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('sub_title')
            ->add('phone')
            ->add('attachments')
            ->add('description')
            ->add('times')
            ->add('country_id')
            ->add('city_id')
            ->add('province_id')
            ->add('district_id')
            ->add('type')
            ->add('limit_number')
            ->add('service_start_date')
            ->add('service_end_date')
            ->add('forms')
            ->add('publish_company')
            ->add('isCharge')
            ->add('price')
            ->add(
                'submit',
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
            'data_class' => 'Sandbox\ApiBundle\Entity\Service\Service',
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
