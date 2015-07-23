<?php

namespace Sandbox\ApiBundle\Form\Room;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RoomAttachmentType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content')
            ->add('attachment_type')
            ->add('filename')
            ->add('preview')
            ->add('size', 'integer')
            ->add('room_type', 'choice', [
                'choices' => [
                    'office' => 'office',
                    'meeting' => 'meeting',
                    'flexible' => 'flexible',
                    'fixed' => 'fixed',
                ],
            ])
            ->add('building_id', 'integer')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Room\RoomAttachment',
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
