<?php

namespace Sandbox\AdminApiBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncJmessageUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('sandbox:api-bundle:sync:JemssageUser')
            ->setDescription('Sync Jmessage User Data');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $service = $this->getContainer()->get('sandbox_api.jmessage_property');

        $salesAdmins = $em->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')
            ->findAll();

        foreach ($salesAdmins as $admin) {
            $userId = $admin->getUserId();
            $xmpp = $admin->getXmppUsername();

            $profiles = $em->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
                ->findBy(array('userId' => $userId));

            $data = [];
            $options = [];
            foreach ($profiles as $profile) {
                $companyId = $profile->getSalesCompanyId();
                if (is_null($companyId)) {
                    $options['nickname'] = $profile->getNickname();
                    $options['avatar'] = $profile->getAvatar();
                } else {
                    $data['name-'.$companyId] = $profile->getNickname();

                    if ($profile->getAvatar()) {
                        $data['avatar-'.$companyId] = $profile->getAvatar();
                    }
                }
            }

            $options['extras'] = $data;

            $service->updateUserInfo($xmpp, $options);
        }

        $output->writeln('Sync Success!');
    }
}
