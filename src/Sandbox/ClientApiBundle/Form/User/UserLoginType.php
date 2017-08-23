<?php

namespace Sandbox\ClientApiBundle\Form\User;

use Sandbox\ClientApiBundle\Form\ThirdParty\ThirdPartyOAuthLoginWeChatType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserLoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('client', new UserClientType())
            ->add('device', new UserLoginDeviceType())
            ->add('wechat',
                new ThirdPartyOAuthLoginWeChatType(),
                array(
                    'required' => false,
                )
            )
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\ClientApiBundle\Data\User\UserLoginData',
        ));
    }

    public function getName()
    {
        return '';
    }
}
