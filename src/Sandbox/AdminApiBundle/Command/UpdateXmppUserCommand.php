<?php

namespace Sandbox\AdminApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateXmppUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('sandbox:api-bundle:update:xmpp_user')
            ->setDescription('Update Xmpp User')
            ->addArgument('userId', InputArgument::REQUIRED, 'user ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arguments = $input->getArguments();
        $userId = $arguments['userId'];

        $em = $this->getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository('SandboxApiBundle:User\User')->find($userId);
        $profile = $em->getRepository('SandboxApiBundle:User\UserProfile')->findOneBy(array('userId' => $userId));
        $name = $profile->getName();

        $xmppUserName = $user->getXmppUsername();

        $this->updateXmppUser($xmppUserName, null, $name);

        $output->writeln('Update Success!');
    }

    private function updateXmppUser(
        $xmppUserName,
        $password,
        $name
    ) {
        $service = $this->getContainer()->get('openfire.service');
        $service->editUser($xmppUserName, $password, $name);
    }
}
