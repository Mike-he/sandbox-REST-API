<?php

namespace Sandbox\ClientApiBundle\Form\ChatGroup;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChatGroupDataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('member_ids')
            ->add('name')
            ->add('platform')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ClientApiBundle\Data\ChatGroup\ChatGroupData',
        ));
    }

    public function getName()
    {
        return '';
    }
}
