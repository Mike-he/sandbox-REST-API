<?php

namespace Sandbox\SalesApiBundle\Controller\Lease;

use AllanSimon\TestHelpers\ApiHelpersTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Tests\Traits\CommonTestsUtilsTrait;

class ClientLeaseBillControllerTest extends WebTestCase
{
    use ApiHelpersTrait;
    use CommonTestsUtilsTrait;

    const LEASE_BILL_FIELDS_AMOUNT = 1;

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
            'Sandbox\ApiBundle\DataFixtures\ORM\Lease\LoadLeaseData',
        ];

        $fixtureExecutor = $this->loadFixtures($fixtures);
        $this->fixtures = $fixtureExecutor->getReferenceRepository();
        $this->em = $fixtureExecutor->getObjectManager();
    }

    public function testGetMyLeaseBillsWithoutAuthenticationShouldNotWork()
    {
        $this->performGetMyBills();

        $this->assertPermissionDenied();
    }

    public function testGetMyLeaseBillsShouldReturnCorrectFieldsAmount()
    {
        $this->givenLoggedInAs('client-mike', 'user-token-mike');

        $this->performGetMyBills();

        $this->assertResponseHasSpecificItemsAmountArray(
            self::LEASE_BILL_FIELDS_AMOUNT
        );
    }

    public function testMyLeaseBillsShouldReturnCorrectDataStructure()
    {
        $this->givenLoggedInAs('client-mike', 'user-token-mike');

        $this->performGetMyBills();

        $data = $this->buildingBillsData();

        $this->assertResponseContainsCorrectDataFields($data);
    }

    // conveniency methods

    private function performGetMyBills()
    {
        $this->performGET('/client/leases/bills/my');
    }

    private function buildingBillsData()
    {
        $this->given('lease_bill_for_type_other');
        $bill = $this->entity;

        $this->given('first-room-type');
        $roomType = $this->entity;

        $this->given('room-building-for-data-structure');
        $building = $this->entity;

        $this->given('sales-company-service-for-longterm');
        $service = $this->entity;

        $data = array(
            [
                'id' => $bill->getId(),
                'serial_number' => $bill->getserialNumber(),
                'name' => $bill->getName(),
                'creation_date' => $bill->getCreationDate()->format("Y-m-d\TH:i:sO"),
                'status' => $bill->getStatus(),
                'start_date' => $bill->getStartDate()->format("Y-m-d\TH:i:sO"),
                'end_date' => $bill->getEndDate()->format("Y-m-d\TH:i:sO"),
                'amount' => (float) $bill->getAmount(),
                'revised_amount' => (float) $bill->getRevisedAmount(),
                'description' => $bill->getDescription(),
                'room_type' => $this->getContainer()->get('translator')
                    ->trans(ProductOrderExport::TRANS_ROOM_TYPE.$roomType->getName()),
                'address' => $building->getCity()->getName().$building->getAddress(),
                'content' => '',
                'preview' => '',
                'transfer' => [],
                'collection_method' => $service->getCollectionMethod(),
            ],
        );

        return $data;
    }
}
