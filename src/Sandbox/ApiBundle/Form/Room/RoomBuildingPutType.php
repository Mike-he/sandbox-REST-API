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
            ->add('subtitle')
            ->add('detail')
            ->add('avatar')
            ->add('city_id')
            ->add('district_id',
                null,
                array(
                    'required' => false,
                ))
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
            ->add(
                'building_services',
                null,
                array(
                    'required' => false,
                )
            )
            ->add('lessor_name')
            ->add('lessor_address')
            ->add('lessor_contact')
            ->add('lessor_phone')
            ->add('lessor_email')
            ->add('lessor_bank_account_name')
            ->add('lessor_bank_account_number')
            ->add('lessor_bank_name')
            ->add('lease_remarks')
            ->add('postal_code')
            ->add('community_manager_name')
            ->add('customer_services')
            ->add('property_type_id')
            ->add('remove_dates')
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
