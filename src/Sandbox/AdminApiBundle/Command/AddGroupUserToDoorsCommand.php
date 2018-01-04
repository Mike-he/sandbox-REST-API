<?php

namespace Sandbox\AdminApiBundle\Command;

use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Controller\Door\DoorController;
use Sandbox\ApiBundle\Entity\User\UserGroup;
use Sandbox\ApiBundle\Traits\DoorAccessTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddGroupUserToDoorsCommand extends ContainerAwareCommand
{
    use DoorAccessTrait;

    const SANDBOX_CUSTOM_HEADER = 'Sandbox-Auth: ';
    const HTTP_STATUS_OK = 200;

    protected function configure()
    {
        $this->setName('sandbox:api-bundle:add_group_user_to_doors')
            ->setDescription('Check group users and add user to doors');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $groups = $em->getRepository('SandboxApiBundle:User\UserGroup')
            ->findBy(array('type' => UserGroup::TYPE_CARD));

        $now = new \DateTime('now');

        $interval = new \DateInterval('PT1H');
        $endDate = new \DateTime();
        $endDate->add($interval);

        $addData = array();
        foreach ($groups as $group) {
            $groupId = $group->getId();

            $addUsers = $em->getRepository('SandboxApiBundle:User\UserGroupHasUser')
                ->findValidUsers(
                    $groupId,
                    $endDate
                );

            $addData[] = array(
                'group_id' => $groupId,
                'users' => $addUsers,
            );
        }

        foreach ($addData as $data) {
            $groupId = $data['group_id'];
            $users = $data['users'];

            $buildingIds = $em->getRepository('SandboxApiBundle:User\UserGroupDoors')
                ->getBuildingIdsByGroup(
                    $groupId
                );

            foreach ($buildingIds as $buildingId) {
                $building = $em->getRepository('SandboxApiBundle:Room\RoomBuilding')
                    ->find($buildingId);

                $base = $building->getServer();
                if ($base) {
                    foreach ($users as $user) {
                        $result = $this->getCardNoByUser($user);
                        if (
                            !is_null($result) && !empty($result) &&
                            DoorController::STATUS_AUTHED === $result['status']
                        ) {
                            $this->setMembershipEmployeeCardForOneBuilding(
                                $base,
                                $user,
                                $result['card_no']
                            );
                        }
                    }
                    $em->flush();
                }
            }
        }

        $output->writeln('Finished !');
    }

    private function getCardNoByUser(
        $userId
    ) {
        $twig = $this->getContainer()->get('twig');
        $globals = $twig->getGlobals();

        $key = $globals['sandbox_auth_key'];

        $contentMd5 = md5($key);

        // CRM API URL
        $apiUrl = $globals['crm_api_url'].
            $globals['crm_api_admin_user_account_cardno'];
        $apiUrl = preg_replace('/{userId}.*?/', "$userId", $apiUrl);

        // init curl
        $ch = curl_init($apiUrl);

        $response = $this->callAPI(
            $ch,
            'GET',
            array(self::SANDBOX_CUSTOM_HEADER.$contentMd5)
        );

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (self::HTTP_STATUS_OK != $httpCode) {
            return;
        }

        $result = json_decode($response, true);

        return $result;
    }

    private function callAPI(
        $ch,
        $method,
        $headers = null,
        $data = null
    ) {
        if ('POST' === $method) {
            curl_setopt($ch, CURLOPT_POST, 1);
        } elseif ('PUT' === $method || 'DELETE' === $method) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        if (is_null($headers)) {
            $headers = array();
        }
        $headers[] = 'Accept: application/json';

        if (!is_null($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $headers[] = 'Content-Type: application/json';
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        return curl_exec($ch);
    }
}
