<?php

namespace Sandbox\AdminApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sandbox\ApiBundle\Traits\DoorAccessTrait;
use Sandbox\ApiBundle\Constants\DoorAccessConstants;

class UpdateCardStatusCommand extends ContainerAwareCommand
{
    use DoorAccessTrait;

    protected function configure()
    {
        $this->setName('Card:Update')
            ->setDescription('Update user card status in door access')
            ->addArgument('userId', InputArgument::REQUIRED, 'user ID')
            ->addArgument('cardNo', InputArgument::REQUIRED, 'user card number')
            ->addArgument('oldCardNo', InputArgument::REQUIRED, 'old user card number')
            ->addArgument('method', InputArgument::REQUIRED, 'update status method');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arguments = $input->getArguments();
        $userId = $arguments['userId'];
        $cardNo = $arguments['cardNo'];
        $method = $arguments['method'];
        $oldCardNo = $arguments['oldCardNo'];

        $myCardNo = $cardNo;
        if (!is_null($oldCardNo)) {
            $myCardNo = $oldCardNo;
        }

        try {
            $userProfile = $this->getContainer()
                ->get('doctrine')
                ->getRepository('SandboxApiBundle:User\UserProfile')
                ->findOneByUserId($userId);

            $userName = $userProfile->getName();
            $this->updateEmployeeCardStatus(
                $userId,
                $userName,
                $myCardNo,
                DoorAccessConstants::METHOD_ADD
            );
            sleep(1);

            // update card to lost
            $this->updateEmployeeCardStatus(
                $userId,
                '',
                $cardNo,
                $method
            );
        } catch (\Exception $e) {
            error_log('Set door access went wrong!');
        }
    }
}
