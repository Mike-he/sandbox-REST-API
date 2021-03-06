<?php

namespace Sandbox\AdminApiBundle\Tests\Controller\Space;

use AllanSimon\TestHelpers\ApiHelpersTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Sandbox\ApiBundle\Tests\Traits\CommonTestsUtilsTrait;
use Sandbox\ApiBundle\Constants\ProductOrderExport;

class AdminCommunityControllerTest extends WebTestCase
{
    use ApiHelpersTrait;
    use CommonTestsUtilsTrait;

    const SPACE_FIELDS_AMOUNT = 11;

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
        $this->performAdminGetSpacesByCommunity($this->entity->getId());

        $this->assertPermissionDenied();
    }

    public function testGetSpacesByCommunityWithoutPermissionShouldNotWork()
    {
        $this->givenLoggedInAs('client-sales-user-without-position', 'sales-user-without-position-token');

        $this->given('room-building-for-data-structure');
        $this->performAdminGetSpacesByCommunity($this->entity->getId());

        $this->assertPermissionDenied();
    }

    public function testGetSpacesByCommunityFirstItemShouldReturnCorrectFieldsAmount()
    {
        $this->givenLoggedInAs('client-mike', 'user-token-mike');

        $this->given('room-building-for-data-structure');
        $this->performAdminGetSpacesByCommunity($this->entity->getId());

        $this->assertResponseFirstItemContainsCorrectFieldsAmount(
            self::SPACE_FIELDS_AMOUNT
        );
    }

    public function testGetSpacesByCommunityWhenTheRoomTypeIsNotFixedShouldReturnCorrectDataStructure()
    {
        $this->givenLoggedInAs('client-mike', 'user-token-mike');

        $this->given('room-building-for-data-structure');
        $this->performAdminGetSpacesByCommunity($this->entity->getId(), 1);

        $data = $this->buildingNonFixedSpaceData();

        $this->assertResponseFirstItemContainsCorrectDataFields($data);
    }

    public function testGetSpacesByCommunityWhenTheRoomTypeIsFixedShouldReturnCorrectDataStructure()
    {
        $this->givenLoggedInAs('client-mike', 'user-token-mike');

        $limit = 1;
        $paramRoomType = '&room_types[]=desk';

        $this->given('room-building-for-data-structure');
        $this->performAdminGetSpacesByCommunity(
            $this->entity->getId(),
            $limit,
            $paramRoomType
        );

        $data = $this->buildingFixedSpaceData();

        $this->assertResponseFirstItemContainsCorrectDataFields($data);
    }

    public function testGetSpacesByCommunityWhenCommunityWithoutSpaceShouldReturnEmptyItemArray()
    {
        $this->givenLoggedInAs('client-mike', 'user-token-mike');

        $this->given('room-building-without-room');
        $this->performAdminGetSpacesByCommunity($this->entity->getId());

        $this->assertResponseIsAnEmptyArray();
    }

    public function testGetSpacesByCommunityWhenCommunityWithManyItemsButLimitOneShouldReturnOneItemArray()
    {
        $this->givenLoggedInAs('client-mike', 'user-token-mike');

        $this->given('room-building-for-data-structure');
        $this->performAdminGetSpacesByCommunity($this->entity->getId(), 1);

        $this->assertResponseHasSpecificItemsAmountArray(1);
    }

    public function testGetSpacesByCommunityWhenCommunityWithManySpacesShouldReturnManyItemsArray()
    {
        $this->givenLoggedInAs('client-mike', 'user-token-mike');

        $this->given('room-building-for-data-structure');
        $this->performAdminGetSpacesByCommunity($this->entity->getId());

        $this->assertResponseHasSpecificItemsAmountArray(5);
    }

    public function testGetCommunitiesByCompanyWithoutAuthenticationShouldNotWork()
    {
        $this->given('sales-company-sandbox');
        $this->performAdminGetCommunities($this->entity->getId());

        $this->assertPermissionDenied();
    }

    public function testGetCommunitiesByCompanyWithoutPermissionShouldNotWork()
    {
        $this->givenLoggedInAs('client-sales-user-without-position', 'sales-user-without-position-token');

        $this->given('sales-company-sandbox');
        $this->performAdminGetCommunities($this->entity->getId());

        $this->assertPermissionDenied();
    }

    public function testGetRoomTypesByCommunityWithoutAuthenticationShouldNotWork()
    {
        $this->given('room-building-for-data-structure');
        $this->performAdminGetRoomTypesByCommunity($this->entity->getId());

        $this->assertPermissionDenied();
    }

    public function testGetRoomTypesByCommunityWithoutPermissionShouldNotWork()
    {
        $this->givenLoggedInAs('client-2', 'user-token-2');

        $this->given('room-building-for-data-structure');
        $this->performAdminGetRoomTypesByCommunity($this->entity->getId());

        $this->assertPermissionDenied();
    }

    public function testGetRoomTypesByCommunityShouldReturnCorrectDataStructure()
    {
        $this->givenLoggedInAs('client-mike', 'user-token-mike');

        $this->given('room-building-for-data-structure');
        $this->performAdminGetRoomTypesByCommunity($this->entity->getId());

        $data = $this->buildingRoomTypesData();

        $this->assertResponseContainsCorrectDataFields($data);
    }

    // conveniency methods

    private function performAdminGetSpacesByCommunity($id, $limit = 5, $roomType = null)
    {
        $this->performGET('/admin/space/communities/spaces?building='.$id.'&pageIndex=1&pageLimit='.$limit.$roomType);
    }

    private function performAdminGetCommunities($id)
    {
        $this->performGET('/admin/space/communities?company='.$id);
    }

    private function performAdminGetRoomTypesByCommunity($id)
    {
        $this->performGET('/admin/space/community/'.$id.'/roomtypes');
    }

    private function buildingNonFixedSpaceData()
    {
        $this->given('product-for-get-spaces-data-structure');
        $product = $this->entity;

        $this->given('room-for-get-spaces-data-structure');
        $room = $this->entity;

        $this->given('room-attachment-1');
        $roomAttachment = $this->entity;

        $data = [
            'id' => $room->getId(),
            'name' => $room->getName(),
            'building_id' => $room->getBuildingId(),
            'type' => $room->getType(),
            'area' => $room->getArea(),
            'allowed_people' => $room->getAllowedPeople(),
            'preview' => $roomAttachment->getPreview(),
            'content' => $roomAttachment->getContent(),
            'product' => [
                'id' => $product->getId(),
                'visible' => $product->getVisible(),
                'start_date' => $product->getStartDate()->format("Y-m-d\TH:i:sO"),
                'recommend' => $product->isRecommend(),
                'sales_recommend' => $product->isSalesRecommend(),
                'favorite' => 0,
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
            'area' => $room->getArea(),
            'allowed_people' => $room->getAllowedPeople(),
            'preview' => $roomAttachment->getPreview(),
            'content' => $roomAttachment->getContent(),
            'product' => [
                'id' => $product->getId(),
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
                'sales_recommend' => $product->isSalesRecommend(),
                'favorite' => 0,
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

        $this->given('second-room-type');
        $roomType2 = $this->entity;

        $this->given('third-room-type');
        $roomType3 = $this->entity;

        $imageUrl = $this->getContainer()->getParameter('image_url');

        $data = [
            [
                'id' => $roomType1->getId(),
                'type' => $roomType1->getName(),
                'name' => $this->getContainer()->get('translator')
                    ->trans(ProductOrderExport::TRANS_ROOM_TYPE.$roomType1->getName()),
                'icon' => $imageUrl.$roomType1->getIcon(),
                'building_id' => $building->getId(),
                'using_number' => 2,
                'all_number' => 4,
            ],
            [
                'id' => $roomType2->getId(),
                'type' => $roomType2->getName(),
                'name' => $this->getContainer()->get('translator')
                    ->trans(ProductOrderExport::TRANS_ROOM_TYPE.$roomType2->getName()),
                'icon' => $imageUrl.$roomType2->getIcon(),
                'building_id' => $building->getId(),
                'using_number' => 2,
                'all_number' => 2,
            ],
            [
                'id' => $roomType3->getId(),
                'type' => $roomType3->getName(),
                'name' => $this->getContainer()->get('translator')
                    ->trans(ProductOrderExport::TRANS_ROOM_TYPE.$roomType3->getName()),
                'icon' => $imageUrl.$roomType3->getIcon(),
                'building_id' => $building->getId(),
                'using_number' => 0,
                'all_number' => 1,
            ],
        ];

        return $data;
    }

    // asserts
}
