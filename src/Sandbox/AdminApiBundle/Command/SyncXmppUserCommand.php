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

        $em = $this->getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository('SandboxApiBundle:User\User')->find($userId);

        $xmppUserName = $user->getXmppUsername();
        $password = $user->getPassword();

        $this->createXmppUser($xmppUserName, $password);

        $output->writeln('Sync Success!');
    }

    private function createXmppUser(
        $xmppUserName,
        $password
    ) {
        $service = $this->getContainer()->get('openfire.service');
        $service->createUser($xmppUserName, $password);
    }
}
