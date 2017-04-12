<?php

namespace Sandbox\AdminApiBundle\Command;

use Sandbox\ApiBundle\Entity\User\UserGroup;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckGroupUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('group-user:check')
            ->setDescription('Check Group Users');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $memberships = $em->getRepository('SandboxApiBundle:MembershipCard\MembershipCardAccessNo')->findAll();
        foreach ($memberships as $membership) {
            //todo:  remove old access no


            $em->remove($membership);
        }
        $em->flush();


        $groups = $em->getRepository('SandboxApiBundle:User\UserGroup')
            ->findBy(array('type' => UserGroup::TYPE_CARD));

        $now = new \DateTime('now');

        foreach ($groups as $group) {
            $groupId = $group->getId();

            $groupUsers = $this->getContainer()->get('doctrine')
                ->getRepository('SandboxApiBundle:User\UserGroupHasUser')
                ->findFinishedUsers($groupId, $now);

            $removeData = array();
            foreach ($groupUsers as $groupUser) {
                $removeData[] = array(
                    'group' => $groupId,
                    'user' => $groupUser->getUserId(),
                );

                $em->remove($groupUser);
            }
        }

        $em->flush();

        foreach ($removeData as $data) {
            $groupUser = $this->getContainer()->get('doctrine')
                ->getRepository('SandboxApiBundle:User\UserGroupHasUser')
                ->findOneBy(array(
                    'groupId' => $data['group'],
                    'userId' => $data['user'],
                ));

            if (!$groupUser) {
                // todo: remove door access
            }
        }
    }
}
