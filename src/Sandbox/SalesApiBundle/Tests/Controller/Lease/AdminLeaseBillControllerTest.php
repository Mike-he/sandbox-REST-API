<?php

namespace Sandbox\SalesApiBundle\Tests\Controller\Lease;

use AllanSimon\TestHelpers\ApiHelpersTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Sandbox\ApiBundle\Tests\Traits\CommonTestsUtilsTrait;

class AdminLeaseBillControllerTest extends WebTestCase
{
    use ApiHelpersTrait;
    use CommonTestsUtilsTrait;

    const LEASE_BILL_FIELDS_AMOUNT = 2;

    public function setUp()
    {
        $this->client = static::createClient();

        $fixtures = [
            'Sandbox\ApiBundle\DataFixtures\ORM\User\LoadUserData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Sales\LoadSalesCompanyData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Admin\LoadAdminPositionData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Admin\LoadAdminPositionBindingData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Admin\LoadAdminPermissionData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Room\LoadRoomCityData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Room\LoadRoomBuildingData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Room\LoadRoomTypesData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Room\LoadRoomData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Room\LoadRoomFixedData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Product\LoadProductData',
            'Sandbox\ApiBundle\DataFixtures\ORM\User\LoadUserCustomerData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Lease\LoadLeaseData',
        ];

        $fixtureExecutor = $this->loadFixtures($fixtures);
        $this->fixtures = $fixtureExecutor->getReferenceRepository();
        $this->em = $fixtureExecutor->getObjectManager();
    }

    public function testGetLeaseBillsWithoutAuthenticationShouldNotWork()
    {
        $this->given('lease_one');
        $this->performSalesGetBills($this->entity->getId());

        $this->assertPermissionDenied();
    }

    public function testGetLeaseBillsWithoutPermissionShouldNotWork()
    {
        $this->givenLoggedInAs('client-sales-user-without-position', 'sales-user-without-position-token');

        $this->given('lease_one');
        $this->performSalesGetBills($this->entity->getId());

        $this->assertPermissionDenied();
    }

    public function testGetLeaseBillsShouldReturnCorrectFieldsAmount()
    {
        $this->givenLoggedInAs('client-2', 'user-token-2');

        $this->given('lease_one');
        $this->performSalesGetBills($this->entity->getId());

        $this->assertResponseHasSpecificItemsAmountArray(
            self::LEASE_BILL_FIELDS_AMOUNT
        );
    }

    public function testGetBillWithoutAuthenticationShouldNotWork()
    {
        $this->given('lease_bill_for_type_lease');
        $this->performSalesGetOneBill($this->entity->getId());

        $this->assertPermissionDenied();
    }

    public function testGetBillWithoutPermissionShouldNotWork()
    {
        $this->givenLoggedInAs('client-sales-user-without-position', 'sales-user-without-position-token');

        $this->given('lease_bill_for_type_lease');
        $this->performSalesGetOneBill($this->entity->getId());

        $this->assertPermissionDenied();
    }

    // conveniency methods

    private function performSalesGetBills($id)
    {
        $this->performGET('/sales/admin/leases/'.$id.'/bills');
    }

    private function performSalesGetOneBill($id)
    {
        $this->performGET('/sales/admin/leases/bills/'.$id);
    }
}
