<?php

namespace Sandbox\ApiBundle\Form\Lease;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LeaseBillPatchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('revised_amount')
            ->add('revision_note')
            ->add('status')
            ->add('remark')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Lease\LeaseBill',
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
