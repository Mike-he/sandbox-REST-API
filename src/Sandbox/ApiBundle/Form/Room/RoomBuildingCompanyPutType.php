<?php

namespace Sandbox\ApiBundle\Form\Room;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RoomBuildingCompanyPutType extends AbstractType
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
                'website',
                null,
                array(
                    'required' => false,
                )
            )
            ->add(
                'phone',
                null,
                array(
                    'required' => false,
                )
            )
            ->add(
                'email',
                null,
                array(
                    'required' => false,
                )
            )
            ->add(
                'remark',
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
            'data_class' => 'Sandbox\ApiBundle\Entity\Room\RoomBuildingCompany',
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
