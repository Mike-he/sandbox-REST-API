<?php

namespace Sandbox\AdminApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncXmppUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('sandbox:api-bundle:sync:xmpp_user')
            ->setDescription('Sync Xmpp User')
            ->addArgument('userId', InputArgument::REQUIRED, 'user ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arguments = $input->getArguments();
        $userId = $arguments['userId'];

        $service = $this->getContainer()->get('openfire.service');
        $em = $this->getContainer()->get('doctrine')->getManager();

        if ($userId == 'all') {
            $users = $em->getRepository('SandboxApiBundle:User\User')->findAll();
            foreach ($users as $user) {
                $xmppUserName = $user->getXmppUsername();
                $password = $user->getPassword();
                $userProfile = $em->getRepository('SandboxApiBundle:User\UserProfile')->findOneBy(array('userId' => $userId));

                $name = $userProfile ? $userProfile->getName() : '';

                $service->syncUser($xmppUserName, $password, $name);
            }
        } else {
            $user = $em->getRepository('SandboxApiBundle:User\User')->find($userId);

            $xmppUserName = $user->getXmppUsername();
            $password = $user->getPassword();

            $userProfile = $em->getRepository('SandboxApiBundle:User\UserProfile')->findOneBy(array('userId' => $userId));

            $name = $userProfile ? $userProfile->getName() : '';

            // Sync User
            $service->syncUser($xmppUserName, $password, $name);
        }

        $output->writeln('Sync Success!');
    }
}
