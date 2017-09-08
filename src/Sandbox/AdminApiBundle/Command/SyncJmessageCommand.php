<?php

namespace Sandbox\AdminApiBundle\Command;

use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Entity\Message\JMessageHistory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncJmessageCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('sandbox:api-bundle:sync:Jemssage')
            ->setDescription('Sync Jmessage Data');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $service = $this->getContainer()->get('sandbox_api.jmessage');

        $statDate = new \DateTime();
        $interval = new \DateInterval('P2H');
        $statDate->sub($interval);
        $endDate = new \DateTime();

        $beginTime = $statDate->format('Y-m-d H:i:s');
        $endTime = $endDate->format('Y-m-d H:i:s');

        $history = $service->getMessages(
            $beginTime,
            $endTime
        );

        $messages = $history['body']['messages'];

        foreach ($messages as $message) {
            $jmessage = $em->getRepository('SandboxApiBundle:Message\JMessageHistory')
                ->findOneBy(array(
                    'msgId' => $message['msgid'],
                    'msgCtime' => $message['msg_ctime'],
                ));

            if (!$jmessage) {
                $jmessage = new JMessageHistory();
                $jmessage->setFromId($message['from_id']);
                $jmessage->setMsgBody(json_encode($message['msg_body']));
                $jmessage->setMsgCtime($message['msg_ctime']);
                $jmessage->setFromAppKey($message['from_appkey']);
                $jmessage->setMsgId($message['msgid']);
                $jmessage->setTargetId($message['target_id']);
                $jmessage->setTargetType($message['target_type']);

                $em->persist($jmessage);
            }
        }

        $em->flush();

        $output->writeln('Sync Success!');
    }
}
