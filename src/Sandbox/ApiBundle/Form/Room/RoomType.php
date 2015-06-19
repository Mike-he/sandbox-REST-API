<?php

namespace Sandbox\ApiBundle\Form\Room;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RoomType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('city_id')
            ->add('building_id')
            ->add('floor_id')
            ->add('number')
            ->add('allowed_people')
            ->add('area')
            ->add('office_supplies')
            ->add('type')
            ->add('attachments')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Room\Room'
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
