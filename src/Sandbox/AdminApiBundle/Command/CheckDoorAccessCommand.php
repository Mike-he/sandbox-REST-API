<?php

namespace Sandbox\AdminApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sandbox\ApiBundle\Traits\DoorAccessTrait;
use Sandbox\ApiBundle\Traits\YunPianSms;
use Sandbox\ApiBundle\Constants\BundleConstants;
use Sandbox\ApiBundle\Constants\SMSConstants;
use Symfony\Component\DomCrawler\Crawler;

class CheckDoorAccessCommand extends ContainerAwareCommand
{
    use DoorAccessTrait;
    use YunPianSms;

    protected function configure()
    {
        $this->setName('access:check')
            ->setDescription('Send notifications to building admins if access server crashed')
            ->addArgument('my_argument', InputArgument::OPTIONAL, 'Argument description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $buildings = $this->getRepo('Room\RoomBuilding')->findAll();
        if (empty($buildings)) {
            return;
        }

        foreach ($buildings as $building) {
            try {
                $now = new \DateTime();
                $minutesDiff = 0;
                $globals = $this->getGlobals();
                $range = $globals['door_api_sync_time_range'];

                $server = $building->getServer();
                if (is_null($server) || empty($server)) {
                    continue;
                }

                $response = $this->getLastSyncTime($server);
                if ($response !== false) {
                    $minutesDiff = $this->getDiffInMinutes($response, $now);
                }

                if ($minutesDiff > $range || $response === false) {
                    $buildingPhones = $this->getRepo('Room\RoomBuildingPhones')->findByBuildingId($building->getId());
                    if (empty($buildingPhones)) {
                        continue;
                    }

                    $cityName = $building->getCity()->getName();
                    $buildingName = $building->getName();

                    //send text message to each phone number
                    $text = SMSConstants::HEAD_SANDBOX.$cityName.'，'.$buildingName.SMSConstants::DOOR_ACCESS_ALARM_SMS;
                    foreach ($buildingPhones as $buildingPhone) {
                        $this->send_sms($buildingPhone->getPhone(), $text);
                    }
                }
            } catch (\Exception $e) {
                error_log('check door access went wrong!');
                continue;
            }
        }
    }

    /**
     * @param $response
     *
     * @return int|mixed
     */
    private function getDiffInMinutes(
        $response,
        $now
    ) {
        $crawler = new Crawler($response);
        $content = $crawler->text();
        $syncTime = new \DateTime($content);
        $diff = $syncTime->diff($now);
        $minutesDiff = $diff->days * 24 * 60;
        $minutesDiff += $diff->h * 60;
        $minutesDiff += $diff->i;

        return $minutesDiff;
    }

    /**
     * @param $repo
     *
     * @return mixed
     */
    protected function getRepo(
        $repo
    ) {
        return $this->getContainer()
            ->get('doctrine')
            ->getRepository(BundleConstants::BUNDLE.':'.$repo);
    }

    /**
     * @return mixed
     */
    protected function getGlobals()
    {
        // get globals
        return $this->getContainer()
            ->get('twig')
            ->getGlobals();
    }
}
