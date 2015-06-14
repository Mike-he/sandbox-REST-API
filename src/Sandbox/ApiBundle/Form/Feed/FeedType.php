<?php

namespace Sandbox\ApiBundle\Form\Feed;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FeedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id')
            ->add('content')
            ->add('ownerid')
            ->add('parentid')
            ->add('parenttype')
            ->add('attachments')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Feed',
        ));
    }

    public function getName()
    {
        return '';
    }
}
