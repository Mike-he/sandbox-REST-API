<?php

namespace Sandbox\ApiBundle\Form\SalesAdmin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class SalesCompanyApplyPostType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, ['constraints' => [new NotBlank()]])
            ->add('phone', null, ['constraints' => [new NotBlank()]])
            ->add('website')
            ->add('contacter', null, ['constraints' => [new NotBlank()]])
            ->add('contacter_phone', null, ['constraints' => [new NotBlank()]])
            ->add('contacter_email', null, ['constraints' => [new NotBlank()]])
            ->add('financial_contacter')
            ->add('financial_contacter_phone')
            ->add('financial_contacter_email')
            ->add('address', null, ['constraints' => [new NotBlank()]])
            ->add('description', null, ['constraints' => [new NotBlank()]])
            ->add('room_types', null, ['constraints' => [new NotBlank()]])
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyApply',
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
