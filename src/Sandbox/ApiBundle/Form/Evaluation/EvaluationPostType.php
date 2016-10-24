<?php

namespace Sandbox\ApiBundle\Form\Evaluation;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EvaluationPostType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type')
            ->add('total_star')
            ->add('service_star')
            ->add('environment_star')
            ->add('comment')
            ->add(
                'building_id',
                'integer',
                array('required' => true)
            )
            ->add(
                'product_order_id',
                'integer',
                array('required' => false)
            )
            ->add(
                'attachments',
                null,
                array('required' => false)
            )
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Evaluation\Evaluation',
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
