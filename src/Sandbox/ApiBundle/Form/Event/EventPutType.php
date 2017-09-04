<?php

namespace Sandbox\ApiBundle\Form\Event;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EventPutType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('attachments')
            ->add('description')
            ->add('dates')
            ->add('city_id')
            ->add('address')
            ->add('limit_number')
            ->add(
                'registration_start_date',
                null,
                array(
                    'widget' => 'single_text',
                ))
            ->add(
                'registration_end_date',
                null,
                array(
                    'widget' => 'single_text',
                ))
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
            'data_class' => 'Sandbox\ApiBundle\Entity\Event\Event',
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
