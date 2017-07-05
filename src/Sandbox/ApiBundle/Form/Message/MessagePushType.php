<?php

namespace Sandbox\ApiBundle\Form\Message;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MessagePushType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title')
            ->add('cover', null, array('required' => false))
            ->add('type', null, array('required' => false))
            ->add('action', null, array('required' => false))
            ->add('url', null, array('required' => false))
            ->add('content', null, array('required' => false));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Message\MessageMaterial',
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
