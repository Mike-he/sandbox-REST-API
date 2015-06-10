<?php

namespace Sandbox\ApiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ApprovalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id')
            ->add('title')
            ->add('description')
            ->add('priority')
            ->add('duedate')
            ->add('ownerid')
            ->add('assigneeid')
            ->add('parentid')
            ->add('parenttype')
            ->add('observers')
            ->add('attachments')
            ->add('service')
            ->add('status')
            ->add('creationdate')
            ->add('modificationdate')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ApiBundle\Entity\Approval',
        ));
    }

    public function getName()
    {
        return 'Sandbox_apibundle_approval';
    }
}
