<?php

namespace Sandbox\ApiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GroupType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('companyid')
            ->add('joinable', 'choice', array(
                'choices' => array(true, false),
            ))
            ->add('searchable', 'choice', array(
                'choices' => array(true, false),
            ))
            ->add('creatorid')
            ->add('creationdate')
            ->add('modificationdate')
            ->add('members')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Group',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Sandbox_apibundle_group';
    }
}
