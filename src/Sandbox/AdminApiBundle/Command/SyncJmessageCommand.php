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
        $commnueService = $this->getContainer()->get('sandbox_api.jmessage_commnue');
        $propertyService = $this->getContainer()->get('sandbox_api.jmessage_property');

        $statDate = new \DateTime();
        $interval = new \DateInterval('PT2H');
        $statDate->sub($interval);
        $endDate = new \DateTime();

        $beginTime = $statDate->format('Y-m-d H:i:s');
        $endTime = $endDate->format('Y-m-d H:i:s');

        // sync sandbox message
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
                $jmessage->setMsgBody(json_encode($message['msg_body'], JSON_UNESCAPED_UNICODE));
                $jmessage->setMsgCtime($message['msg_ctime']);
                $jmessage->setFromAppKey($message['from_appkey']);
                $jmessage->setMsgId($message['msgid']);
                $jmessage->setTargetId($message['target_id']);
                $jmessage->setTargetType($message['target_type']);
                $jmessage->setMsgType($message['msg_type']);

                $em->persist($jmessage);
            }
        }

        // sync Property message
        $propertyHistory = $propertyService->getMessages(
            $beginTime,
            $endTime
        );

        $propertyMessages = $propertyHistory['body']['messages'];

        foreach ($propertyMessages as $message) {
            $jmessage = $em->getRepository('SandboxApiBundle:Message\JMessageHistory')
                ->findOneBy(array(
                    'msgId' => $message['msgid'],
                    'msgCtime' => $message['msg_ctime'],
                ));

            if (!$jmessage) {
                $jmessage = new JMessageHistory();
                $jmessage->setFromId($message['from_id']);
                $jmessage->setMsgBody(json_encode($message['msg_body'], JSON_UNESCAPED_UNICODE));
                $jmessage->setMsgCtime($message['msg_ctime']);
                $jmessage->setFromAppKey($message['from_appkey']);
                $jmessage->setMsgId($message['msgid']);
                $jmessage->setTargetId($message['target_id']);
                $jmessage->setTargetType($message['target_type']);
                $jmessage->setMsgType($message['msg_type']);

                $em->persist($jmessage);
            }
        }


        // sync commnue message
        $commnueHistory = $commnueService->getMessages(
            $beginTime,
            $endTime
        );

        $commnueMessages = $commnueHistory['body']['messages'];

        foreach ($commnueMessages as $message) {
            $jmessage = $em->getRepository('SandboxApiBundle:Message\JMessageHistory')
                ->findOneBy(array(
                    'msgId' => $message['msgid'],
                    'msgCtime' => $message['msg_ctime'],
                ));

            if (!$jmessage) {
                $jmessage = new JMessageHistory();
                $jmessage->setFromId($message['from_id']);
                $jmessage->setMsgBody(json_encode($message['msg_body'], JSON_UNESCAPED_UNICODE));
                $jmessage->setMsgCtime($message['msg_ctime']);
                $jmessage->setFromAppKey($message['from_appkey']);
                $jmessage->setMsgId($message['msgid']);
                $jmessage->setTargetId($message['target_id']);
                $jmessage->setTargetType($message['target_type']);
                $jmessage->setMsgType($message['msg_type']);

                $em->persist($jmessage);
            }
        }

        $em->flush();

        $output->writeln('Sync Success!');
    }
}
