<?php

namespace Sandbox\ApiBundle\Form\SalesAdmin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;

class SalesFinanceProfileInvoicePostType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',
                TextType::class,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                ))
            ->add('category',
                TextType::class,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                ))
            ->add('taxpayer_id',
                TextType::class,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                ))
            ->add('address',
                TextType::class,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                ))
            ->add('phone',
                TextType::class,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                ))
            ->add('bank_account_name',
                TextType::class,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                ))
            ->add('bank_account_number',
                TextType::class,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyProfileInvoice',
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
