<?php

namespace Sandbox\ApiBundle\Tests\Controller\Space;

use AllanSimon\TestHelpers\ApiHelpersTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Sandbox\ApiBundle\Tests\Traits\CommonTestsUtilsTrait;
use Sandbox\ApiBundle\Traits\HandleCoordinateTrait;

class SpaceControllerTest extends WebTestCase
{
    use ApiHelpersTrait;
    use CommonTestsUtilsTrait;
    use HandleCoordinateTrait;

    const LOCATION_IN_SHANGHAI_LAT = 31.216193;
    const LOCATION_IN_SHANGHAI_LNG = 121.632682;
    const LOCATION_IN_BEIJING_LAT = 39.97758;
    const LOCATION_IN_BEIJING_LNG = 116.366549;
    const NON_EXIST_CITY = 0;
    const SHANGHAI_PUDONG_DISTRICT = '浦东新区';
    const EVALUATION_AMOUNT = 3;
    const COMMUNITIES_SEARCH_FIELDS_AMOUNT = 9;

    public function setUp()
    {
        $this->client = static::createClient();

        $fixtures = [
            'Sandbox\ApiBundle\DataFixtures\ORM\Room\LoadRoomCityData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Room\LoadRoomBuildingData',
            'Sandbox\ApiBundle\DataFixtures\ORM\User\LoadUserData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Evaluation\LoadEvaluationData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Sales\LoadSalesCompanyData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Admin\LoadAdminPositionData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Admin\LoadAdminPositionBindingData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Admin\LoadAdminPermissionData',
        ];

        $fixtureExecutor = $this->loadFixtures($fixtures);
        $this->fixtures = $fixtureExecutor->getReferenceRepository();
        $this->em = $fixtureExecutor->getObjectManager();
    }

    /**
     * Get Sales Administrative Region Should Work.
     */
    public function testGetSalesAdministrativeRegionShouldWork()
    {
        $this->givenLoggedInAs('client-mike', 'user-token-mike');

        $this->performGetSalesAdministrativeRegion();

        $this->assertOkSuccess();
    }

    /**
     * Get Sales Administrative Region With ParentId Should Work.
     */
    public function testGetSalesAdministrativeRegionWithParentIdShouldWork()
    {
        $this->givenLoggedInAs('client-mike', 'user-token-mike');

        $this->given('china');
        $countryId = $this->entity->getId();

        $this->performGetSalesAdministrativeRegion($countryId);

        $this->assertOkSuccess();
    }

    /**
     * Post Sales Communities With Authentication Should Work.
     */
    public function testPostSalesCommunitiesWithAuthenticationShouldWork()
    {
        $this->givenLoggedInAs('client-2', 'user-token-2');

        $postAmount = $this->getCurrentAmountInDatabase('Room\RoomBuilding');

        $data = $this->constructSalesCommunityData();

        $this->performPostSalesCommunities($data);

        $this->assertOkSuccess();

        $this->assertEquals(
            $postAmount + 1,
            $this->getCurrentAmountInDatabase('Room\RoomBuilding'),
            'The posts amount in database is incorrect.'
        );
    }

    /**
     * Put Sales Communities With Authentication Should Work.
     */
    public function testPutSalesCommunitiesWithAuthenticationShouldWork()
    {
        $this->givenLoggedInAs('client-2', 'user-token-2');

        $data = $this->constructSalesCommunityData();

        $this->given('room-building-for-data-structure');
        $buildingId = $this->entity->getId();

        $this->performPutSalesCommunities($buildingId, $data);

        $this->assertNoContentResponse();
    }

    // conveniency methods

    private function performGetSalesAdministrativeRegion(
        $parentId = null
    ) {
        $this->performGET('/sales/admin/space/administrative_region?parent='.$parentId);
    }

    private function performPostSalesCommunities(
        $data
    ) {
        $this->performPOST('/sales/admin/buildings', $data);
    }

    private function performPutSalesCommunities(
        $id,
        $data
    ) {
        $this->performPUT('/sales/admin/buildings/'.$id, $data);
    }

    private function constructSalesCommunityData()
    {
        $this->given('shanghai');
        $cityId = $this->entity->getId();

        $this->given('huangpuqu');
        $districtId = $this->entity->getId();

        return array(
            'name' => 'test',
            'subtitle' => 'sandbox',
            'detail' => 'sandbox detail',
            'avatar' => 'http://...',
            'city_id' => $cityId,
            'district_id' => $districtId,
            'address' => 'zhang jiang',
            'lat' => 31.21,
            'lng' => 121.22,
            'floors' => array(
                'add' => array(),
            ),
            'server' => 'http:...',
            'room_attachments' => array(
                'add' => array(),
            ),
            'building_attachments' => array(),
            'business_hour' => '10:00-22:00',
            'email' => '123@sandbox3.cn',
            'order_remind_phones' => '021-123',
            'phones' => array(
                'add' => array(),
            ),
            'building_company' => array(
                'name' => 'sandbox',
            ),
            'building_services' => array(),
            'lessor_name' => 'sandbox',
            'lessor_address' => 'shanghai',
            'lessor_contact' => 'sandbox',
            'lessor_phone' => '12345678',
            'lessor_email' => 'test@sandbox3.cn',
            'customer_services' => array(
                'add' => array(),
                'remove' => array(),
            ),
        );
    }
}
