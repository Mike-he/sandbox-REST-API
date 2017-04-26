<?php

namespace Sandbox\ClientApiBundle\Form\User;

use Sandbox\ClientApiBundle\Form\ThirdParty\ThirdPartyOAuthLoginWeChatType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RegisterVerifyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('phone')
            ->add('password')
            ->add('code')
            ->add('wechat', new ThirdPartyOAuthLoginWeChatType())
            ->add('phone_code')
            ->add('inviter_user_id')
            ->add('inviter_phone')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ClientApiBundle\Data\User\RegisterVerify',
        ));
    }

    public function getName()
    {
        return '';
    }
}
