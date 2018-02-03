<?php

namespace Sandbox\ApiBundle\Form\Room;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RoomPatchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('type_tag')
            ->add('allowed_people', 'integer')
            ->add('area')
            ->add('office_supplies',
                null,
                array(
                    'mapped' => false,
                )
            )
            ->add('attachments',
                null,
                array(
                    'mapped' => false,
                )
            )
            ->add(
                'room_fixed',
                null,
                array(
                    'mapped' => false,
                )
            )
            ->add(
                'room_meeting',
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
            'data_class' => 'Sandbox\ApiBundle\Entity\Room\Room',
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
