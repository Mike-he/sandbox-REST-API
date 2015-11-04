<?php

namespace Sandbox\ApiBundle\Form\Banner;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BannerType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text')
            ->add('source', 'text')
            ->add(
                'source_id',
                'integer',
                array('required' => false)
            )
            ->add(
                'url',
                'url',
                array(
                    'required' => false,
                    'mapped' => false,
                )
            )
            ->add(
                'banner_attachments',
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
            'data_class' => 'Sandbox\ApiBundle\Entity\Banner\Banner',
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
