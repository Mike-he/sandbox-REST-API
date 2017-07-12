<?php

namespace Sandbox\ApiBundle\Form\ChatGroup;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChatGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('creatorId', null, array('required' => false))
            ->add('buildingId', null, array('required' => false))
            ->add('tag', null, array('required' => false))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\ChatGroup\ChatGroup',
        ));
    }

    public function getName()
    {
        return '';
    }
}
