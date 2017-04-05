<?php

namespace Sandbox\SalesApiBundle\Tests\Controller\Dashboard;

use AllanSimon\TestHelpers\ApiHelpersTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Sandbox\ApiBundle\Tests\Traits\CommonTestsUtilsTrait;

class AdminDashboardControllerTest extends WebTestCase
{
    use ApiHelpersTrait;
    use CommonTestsUtilsTrait;

    const BUILDING_AMOUNT = 4;

    public function setUp()
    {
        $this->client = static::createClient();

        $fixtures = [
            'Sandbox\ApiBundle\DataFixtures\ORM\User\LoadUserData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Sales\LoadSalesCompanyData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Admin\LoadAdminPermissionData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Admin\LoadAdminPositionData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Admin\LoadAdminPositionBindingData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Room\LoadRoomCityData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Room\LoadRoomBuildingData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Room\LoadRoomTypesData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Room\LoadRoomData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Room\LoadRoomFixedData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Product\LoadProductData',
        ];

        $fixtureExecutor = $this->loadFixtures($fixtures);
        $this->fixtures = $fixtureExecutor->getReferenceRepository();
        $this->em = $fixtureExecutor->getObjectManager();
    }

    public function testGetBuildingWithoutAuthenticationShouldNotWork()
    {
        $this->performSalesGetBuilding();

        $this->assertPermissionDenied();
    }

    public function testGetBuildingWithoutPermissionShouldNotWork()
    {
        $this->givenLoggedInAs('client-sales-user-without-position', 'sales-user-without-position-token');

        $this->performSalesGetBuilding();

        $this->assertPermissionDenied();
    }

    public function testGetBuildingFirstItemShouldReturnCorrectDataFields()
    {
        $this->givenLoggedInAs('client-2', 'user-token-2');

        $this->performSalesGetBuilding();

        $data = $this->buildingData();

        $this->assertResponseFirstItemContainsCorrectDataFields($data);
    }

    public function testGetSpacesByCommunityFirstItemShouldReturnCorrectFieldsAmount()
    {
        $this->givenLoggedInAs('client-2', 'user-token-2');

        $this->performSalesGetBuilding();

        $this->assertResponseContainsCorrectFieldsAmount(
            self::BUILDING_AMOUNT
        );
    }

    // conveniency methods

    private function performSalesGetBuilding()
    {
        $this->performGET('/sales/admin/dashboard/buildings');
    }

    private function buildingData()
    {
        $this->given('room-building-for-data-structure');
        $building1 = $this->entity;

        $data = [
            'id' => $building1->getId(),
            'name' => $building1->getName(),
        ];

        return $data;
    }
}
