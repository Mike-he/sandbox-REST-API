<?php

namespace Sandbox\ApiBundle\Form\Lease;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;


class LeaseRequiredType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('building_id',
                IntegerType::class,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                )
            )
            ->add('product_id',
                IntegerType::class,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                )
            )
            ->add('lessor_name',
                TextType::class,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                )
            )
            ->add('lessor_address',
                TextType::class,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                )
            )
            ->add('lessor_contact',
                TextType::class,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                )
            )
            ->add('lessor_phone',
                TextType::class,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                )
            )
            ->add('lessor_email',
                EmailType::class,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                )
            )
            ->add('lessee_type',
                TextType::class,
                    array(
                        'constraints' => array(
                            new NotBlank(),
                        ),
                    )
                )
            ->add('lessee_enterprise')
            ->add('lessee_customer',
                IntegerType::class,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                )
            )
            ->add('start_date',
                TextType::class,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                )
            )
            ->add('end_date',
                TextType::class,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                )
            )
            ->add('monthly_rent',
                MoneyType::class,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                )
            )
            ->add('total_rent',
                MoneyType::class,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                )
            )
            ->add('deposit',
                MoneyType::class,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                )
            )
            ->add('purpose',
                TextareaType::class,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                )
            )
            ->add('other_expenses',
                TextareaType::class,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                )
            )
            ->add('supplementary_terms',
                TextareaType::class,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                )
            )
            ->add('status',
                TextType::class,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                )
            )
            ->add('is_auto')
            ->add('plan_day')
            ->add('lease_clue_id')
            ->add('lease_offer_id')
            ->add('bills',
                null,
                array(
                    'mapped' => false,
                )
            )
            ->add('lease_rent_types',
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
            'data_class' => 'Sandbox\ApiBundle\Entity\Lease\Lease',
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
