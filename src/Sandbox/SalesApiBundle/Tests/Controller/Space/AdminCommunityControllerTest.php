<?php

namespace Sandbox\SaleApiBundle\Tests\Controller\Space;

use AllanSimon\TestHelpers\ApiHelpersTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Tests\Traits\CommonTestsUtilsTrait;

class AdminCommunityControllerTest extends WebTestCase
{
    use ApiHelpersTrait;
    use CommonTestsUtilsTrait;

    const SPACE_FIELDS_AMOUNT = 10;

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
            'Sandbox\ApiBundle\DataFixtures\ORM\Room\LoadRoomAttachmentData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Room\LoadRoomAttachmentBindingData',
        ];

        $fixtureExecutor = $this->loadFixtures($fixtures);
        $this->fixtures = $fixtureExecutor->getReferenceRepository();
        $this->em = $fixtureExecutor->getObjectManager();
    }

    public function testGetSpacesByCommunityWithoutAuthenticationShouldNotWork()
    {
        $this->given('room-building-for-data-structure');
        $this->performSalesGetSpacesByCommunity($this->entity->getId());

        $this->assertPermissionDenied();
    }

    public function testGetSpacesByCommunityWithoutPermissionShouldNotWork()
    {
        $this->givenLoggedInAs('client-sales-user-without-position', 'sales-user-without-position-token');

        $this->given('room-building-for-data-structure');
        $this->performSalesGetSpacesByCommunity($this->entity->getId());

        $this->assertPermissionDenied();
    }

    public function testGetSpacesByCommunityFirstItemShouldReturnCorrectFieldsAmount()
    {
        $this->givenLoggedInAs('client-2', 'user-token-2');

        $this->given('room-building-for-data-structure');
        $this->performSalesGetSpacesByCommunity($this->entity->getId());

        $this->assertResponseFirstItemContainsCorrectFieldsAmount(
            self::SPACE_FIELDS_AMOUNT
        );
    }

    public function testGetSpacesByCommunityWhenTheRoomTypeIsNotFixedShouldReturnCorrectDataStructure()
    {
        $this->givenLoggedInAs('client-2', 'user-token-2');

        $this->given('room-building-for-data-structure');
        $this->performSalesGetSpacesByCommunity($this->entity->getId(), 1);

        $data = $this->buildingNonFixedSpaceData();

        $this->assertResponseFirstItemContainsCorrectDataFields($data);
    }

    public function testGetSpacesByCommunityWhenTheRoomTypeIsFixedShouldReturnCorrectDataStructure()
    {
        $this->givenLoggedInAs('client-2', 'user-token-2');

        $limit = 1;
        $paramRoomType = '&room_types[]=fixed';

        $this->given('room-building-for-data-structure');
        $this->performSalesGetSpacesByCommunity(
            $this->entity->getId(),
            $limit,
            $paramRoomType
        );

        $data = $this->buildingFixedSpaceData();

        $this->assertResponseFirstItemContainsCorrectDataFields($data);
    }

    public function testGetSpacesByCommunityWhenCommunityWithoutSpaceShouldReturnEmptyItemArray()
    {
        $this->givenLoggedInAs('client-2', 'user-token-2');

        $this->given('room-building-without-room');
        $this->performSalesGetSpacesByCommunity($this->entity->getId());

        $this->assertResponseIsAnEmptyArray();
    }

    public function testGetSpacesByCommunityWhenCommunityWithManyItemsButLimitOneShouldReturnOneItemArray()
    {
        $this->givenLoggedInAs('client-2', 'user-token-2');

        $this->given('room-building-for-data-structure');
        $this->performSalesGetSpacesByCommunity($this->entity->getId(), 1);

        $this->assertResponseHasSpecificItemsAmountArray(1);
    }

    public function testGetSpacesByCommunityWhenCommunityWithManySpacesShouldReturnManyItemsArray()
    {
        $this->givenLoggedInAs('client-2', 'user-token-2');

        $this->given('room-building-for-data-structure');
        $this->performSalesGetSpacesByCommunity($this->entity->getId());

        $this->assertResponseHasSpecificItemsAmountArray(5);
    }

    public function testGetCommunitiesWithoutAuthenticationShouldNotWork()
    {
        $this->performSalesGetCommunities();

        $this->assertPermissionDenied();
    }

    public function testGetCommunitiesWithoutPermissionShouldNotWork()
    {
        $this->givenLoggedInAs('client-sales-user-without-position', 'sales-user-without-position-token');

        $this->performSalesGetCommunities();

        $this->assertPermissionDenied();
    }

    public function testGetRoomTypesByCommunityWithoutAuthenticationShouldNotWork()
    {
        $this->given('room-building-for-data-structure');
        $this->performSalesGetRoomTypesByCommunity($this->entity->getId());

        $this->assertPermissionDenied();
    }

    public function testGetRoomTypesByCommunityWithoutPermissionShouldNotWork()
    {
        $this->givenLoggedInAs('client-sales-user-without-position', 'sales-user-without-position-token');

        $this->given('room-building-for-data-structure');
        $this->performSalesGetRoomTypesByCommunity($this->entity->getId());

        $this->assertPermissionDenied();
    }

    public function testGetRoomTypesByCommunityShouldReturnCorrectDataStructure()
    {
        $this->givenLoggedInAs('client-2', 'user-token-2');

        $this->given('room-building-for-data-structure');
        $this->performSalesGetRoomTypesByCommunity($this->entity->getId());

        $data = $this->buildingRoomTypesData();

        $this->assertResponseContainsCorrectDataFields($data);
    }

    // conveniency methods

    private function performSalesGetSpacesByCommunity($id, $limit = 5, $roomType = null)
    {
        $this->performGET('/sales/admin/space/communities/'.$id.'/spaces?&pageIndex=1&pageLimit='.$limit.$roomType);
    }

    private function performSalesGetCommunities()
    {
        $this->performGET('/sales/admin/space/communities');
    }

    private function performSalesGetRoomTypesByCommunity($id)
    {
        $this->performGET('/sales/admin/space/community/'.$id.'/roomtypes');
    }

    private function buildingNonFixedSpaceData()
    {
        $this->given('product-for-get-spaces-data-structure');
        $product = $this->entity;

        $this->given('room-for-get-spaces-data-structure');
        $room = $this->entity;

        $this->given('second-room-type');
        $roomType = $this->entity;

        $this->given('room-attachment-1');
        $roomAttachment = $this->entity;

        $data = [
            'id' => $room->getId(),
            'name' => $room->getName(),
            'building_id' => $room->getBuildingId(),
            'type' => $room->getType(),
            'rent_type' => $roomType->getType(),
            'area' => $room->getArea(),
            'allowed_people' => $room->getAllowedPeople(),
            'preview' => $roomAttachment->getPreview(),
            'content' => $roomAttachment->getContent(),
            'product' => [
                'id' => $product->getId(),
                'unit_price' => $product->getUnitPrice(),
                'visible' => $product->getVisible(),
                'start_date' => $product->getStartDate()->format("Y-m-d\TH:i:sO"),
                'base_price' => $product->getBasePrice(),
                'recommend' => $product->isRecommend(),
            ],
        ];

        return $data;
    }

    private function buildingFixedSpaceData()
    {
        $this->given('product-for-fixed-room-get-spaces-data-structure');
        $product = $this->entity;

        $this->given('fixed-room-for-get-spaces-data-structure');
        $room = $this->entity;

        $this->given('second-room-type');
        $roomType = $this->entity;

        $this->given('room-seat-1');
        $firstRoomSeat = $this->entity;

        $this->given('room-seat-2');
        $secondRoomSeat = $this->entity;

        $this->given('room-attachment-2');
        $roomAttachment = $this->entity;

        $data = [
            'id' => $room->getId(),
            'name' => $room->getName(),
            'building_id' => $room->getBuildingId(),
            'type' => $room->getType(),
            'rent_type' => $roomType->getType(),
            'area' => $room->getArea(),
            'allowed_people' => $room->getAllowedPeople(),
            'preview' => $roomAttachment->getPreview(),
            'content' => $roomAttachment->getContent(),
            'product' => [
                'id' => $product->getId(),
                'unit_price' => $product->getUnitPrice(),
                'visible' => $product->getVisible(),
                'start_date' => $product->getStartDate()->format("Y-m-d\TH:i:sO"),
                'seats' => [
                    [
                        'id' => $firstRoomSeat->getId(),
                        'seat_number' => $firstRoomSeat->getSeatNumber(),
                        'base_price' => $firstRoomSeat->getBasePrice(),
                    ],
                    [
                        'id' => $secondRoomSeat->getId(),
                        'seat_number' => $secondRoomSeat->getSeatNumber(),
                        'base_price' => $secondRoomSeat->getBasePrice(),
                    ],
                ],
                'recommend' => $product->isRecommend(),
            ],
        ];

        return $data;
    }

    private function buildingRoomTypesData()
    {
        $this->given('room-building-for-data-structure');
        $building = $this->entity;

        $this->given('first-room-type');
        $roomType1 = $this->entity;

        $this->given('seventh-room-type');
        $roomType2 = $this->entity;

        $data = [
            [
                'id' => $roomType1->getId(),
                'type' => $roomType1->getName(),
                'name' => $this->getContainer()->get('translator')
                    ->trans(ProductOrderExport::TRANS_ROOM_TYPE.$roomType1->getName()),
                'icon' => $roomType1->getIcon(),
                'building_id' => $building->getId(),
                'using_number' => 1,
                'all_number' => 3,
            ],
            [
                'id' => $roomType2->getId(),
                'type' => $roomType2->getName(),
                'name' => $this->getContainer()->get('translator')
                    ->trans(ProductOrderExport::TRANS_ROOM_TYPE.$roomType2->getName()),
                'icon' => $roomType2->getIcon(),
                'building_id' => $building->getId(),
                'using_number' => 1,
                'all_number' => 1,
            ],
        ];

        return $data;
    }

    // asserts
}
