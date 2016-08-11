<?php

namespace Sandbox\ApiBundle\Form\Room;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RoomBuildingPutType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('detail')
            ->add('avatar')
            ->add('city_id')
            ->add('address')
            ->add('lat')
            ->add('lng')
            ->add('floors')
            ->add(
                'server',
                null,
                array(
                    'required' => false,
                )
            )
            ->add('room_attachments')
            ->add(
                'email',
                'email',
                array(
                    'required' => false,
                )
            )
            ->add(
                'phones',
                null,
                array(
                    'required' => false,
                )
            )
            ->add(
                'business_hour',
                null,
                array(
                    'required' => false,
                )
            )
            ->add('building_attachments')
            ->add('building_company')
            ->add(
                'order_remind_phones',
                null,
                array(
                    'required' => false,
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
            'data_class' => 'Sandbox\ApiBundle\Entity\Room\RoomBuilding',
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
