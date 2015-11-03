<?php

namespace Sandbox\ApiBundle\Form\Feed;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FeedCommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('payload')
            ->add(
                'reply_to_user_id',
                null,
                array('mapped' => false)
            )
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Feed\FeedComment',
        ));
    }

    public function getName()
    {
        return '';
    }
}
