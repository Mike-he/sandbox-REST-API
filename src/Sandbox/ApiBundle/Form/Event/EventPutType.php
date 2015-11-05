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
            ->add(
                'event_attachments',
                null,
                array(
                    'mapped' => false,
                ))
            ->add('description')
            ->add(
                'event_dates',
                null,
                array(
                    'mapped' => false,
                ))
            ->add('city_id')
            ->add('building_id')
            ->add('room_id')
            ->add('limit_number')
            ->add(
                'registration_start_date',
                'date',
                array(
                    'widget' => 'single_text',
                ))
            ->add(
                'registration_end_date',
                'date',
                array(
                    'widget' => 'single_text',
                ))
            ->add(
                'event_forms',
                null,
                array(
                    'mapped' => false,
                ))
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
