<?php

namespace Sandbox\AdminApiBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class SyncJmessageUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('sandbox:api-bundle:sync:JemssageUser')
            ->setDescription('Sync Jmessage User Data')
            ->addArgument('userId', InputArgument::REQUIRED, 'user ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arguments = $input->getArguments();
        $userId = $arguments['userId'];

        $service = $this->getContainer()->get('sandbox_api.jmessage');
        $commnueService = $this->getContainer()->get('sandbox_api.jmessage_commnue');

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $user = $em->getRepository('SandboxApiBundle:User\User')->find($userId);
        $profile = $em->getRepository('SandboxApiBundle:User\UserProfile')
            ->findOneBy(array('userId' => $userId));

        $customers = $em->getRepository('SandboxApiBundle:User\UserCustomer')
            ->findBy(array('userId' => $userId));

        $data = [];
        foreach ($customers as $customer) {
            $companyId = $customer->getCompanyId();
            $data['cname-'.$companyId] = $customer->getName();
            if ($customer->getAvatar()) {
                $data['cavatar-'.$companyId] = $customer->getAvatar();
            }
        }
        $data['phone'] = $user->getPhone();

        $options = array(
            'nickname' => $profile ? $profile->getName() : '',
            'extras' => $data,
        );

        $xmpp = $user->getXmppUsername();

        // sync userinfo to Sandbox3 Jmessage
        $service->updateUserInfo($xmpp, $options);

        // sync userinfo to Commnue Jmessage
        $commnueService->updateUserInfo($xmpp, $options);

        $output->writeln('Sync Success!');
    }
}
