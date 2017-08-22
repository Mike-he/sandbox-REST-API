<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Traits\CurlUtil;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170705012817 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use CurlUtil;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    }

    public function postUp(Schema $schema)
    {
        parent::postUp($schema);

        $em = $this->container->get('doctrine.orm.entity_manager');

        $roomCities = $em->getRepository('SandboxApiBundle:Room\RoomCity')->findBy(array('level' => 4));

//        foreach ($roomCities as $city) {
//            $parentCity = $em->getRepository('SandboxApiBundle:Room\RoomCity')->find($city->getParentId());
//
//            $locationString = $parentCity->getName().$city->getName();
//
//            $ch = curl_init("http://api.map.baidu.com/geocoder/v2/?address=$locationString&output=json&ak=79a033f9382689a99c57e1eb846e6f4f");
//            $re = $this->callAPI($ch, 'GET');
//            $reArray = json_decode($re, true);
//
//            $lng = $reArray['result']['location']['lng'];
//            $lat = $reArray['result']['location']['lat'];
//
//            $city->setLng($lng);
//            $city->setLat($lat);
//        }

        $em->flush();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
