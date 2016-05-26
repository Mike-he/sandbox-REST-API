<?php

namespace Sandbox\ApiBundle\Form\Bulletin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BulletinPostAttachmentForm extends AbstractType
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
            ->add(
                'preview',
                'text',
                array('required' => false)
            )
            ->add('size', 'integer');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Bulletin\BulletinPostAttachment',
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
