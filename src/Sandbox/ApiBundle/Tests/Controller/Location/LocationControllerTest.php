<?php

namespace Sandbox\ApiBundle\Tests\Controller\Location;

use AllanSimon\TestHelpers\ApiHelpersTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Sandbox\ApiBundle\Constants\LocationConstants;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Tests\Traits\CommonTestsUtilsTrait;
use Sandbox\ApiBundle\Traits\HandleCoordinateTrait;

class LocationControllerTest extends WebTestCase
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
    const COMMUNITIES_FILTER_FIELDS_AMOUNT = 3;
    const COMMUNITIES_SEARCH_FIELDS_AMOUNT = 9;

    public function setUp()
    {
        $this->client = static::createClient();

        $fixtures = [
            'Sandbox\ApiBundle\DataFixtures\ORM\Room\LoadRoomCityData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Room\LoadRoomBuildingData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Sales\LoadSalesCompanyData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Room\LoadRoomTypesData',
        ];

        $fixtureExecutor = $this->loadFixtures($fixtures);
        $this->fixtures = $fixtureExecutor->getReferenceRepository();
        $this->em = $fixtureExecutor->getObjectManager();
    }

    public function testGetCommunitiesFilterWithoutAuthenticationShouldWork()
    {
        $this->performGetCommunitiesFilter();

        $this->assertOkSuccess();
    }

    public function testGetCommunitiesFilterShouldReturnCorrectFieldsAmount()
    {
        $this->performGetCommunitiesFilter();

        $this->assertResponseContainsCorrectFieldsAmount(
            self::COMMUNITIES_FILTER_FIELDS_AMOUNT
        );
    }

    public function testGetCommunitiesFilterShouldReturnCorrectDataStructure()
    {
        $this->performGetCommunitiesFilter();

        $data = $this->buildFilterData();

        $this->assertResponseContainsCorrectDataFields($data);
    }

    public function testGetCommunitiesSearchWithoutAuthenticationShouldWork()
    {
        $this->given('shanghai');

        $this->performGetCommunitiesSearch(
            $this->entity->getId(),
            self::LOCATION_IN_SHANGHAI_LAT,
            self::LOCATION_IN_SHANGHAI_LNG
        );

        $this->assertOkSuccess();
    }

    public function testGetCommunitiesSearchFirstItemShouldReturnCorrectFieldsAmount()
    {
        $this->given('beijing');

        $this->performGetCommunitiesSearch(
            $this->entity->getId(),
            self::LOCATION_IN_SHANGHAI_LAT,
            self::LOCATION_IN_SHANGHAI_LNG
        );

        $this->assertResponseFirstItemContainsCorrectFieldsAmount(
            self::COMMUNITIES_SEARCH_FIELDS_AMOUNT
        );
    }

    public function testGetCommunitiesSearchFirstItemShouldReturnCorrectDataStructure()
    {
        $this->given('shanghai');

        $this->performGetCommunitiesSearch(
            $this->entity->getId(),
            self::LOCATION_IN_SHANGHAI_LAT,
            self::LOCATION_IN_SHANGHAI_LNG
        );

        $this->given('room-building-for-data-structure');
        $roomBuilding = $this->entity;

        $this->given('first-attachment-for-building-1');
        $roomBuildingAttachment = $this->entity;
        $cover = $roomBuildingAttachment->getContent();

        $distance = $this->calculateDistanceBetweenCoordinates(
            self::LOCATION_IN_SHANGHAI_LAT,
            self::LOCATION_IN_SHANGHAI_LNG,
            $roomBuilding->getLat(),
            $roomBuilding->getLng()
        );

        $data = [
            'id' => $roomBuilding->getId(),
            'name' => $roomBuilding->getName(),
            'distance' => $distance,
            'avatar' => $roomBuilding->getAvatar(),
            'lat' => $roomBuilding->getLat(),
            'lng' => $roomBuilding->getLng(),
            'cover' => $cover,
            'location' => self::SHANGHAI_PUDONG_DISTRICT,
            'total_evaluation_number' => (string) (
                $roomBuilding->getOrderEvaluationNumber() +
                $roomBuilding->getBuildingEvaluationNumber()
            ),
        ];

        $this->assertResponseFirstItemContainsCorrectDataFields($data);
    }

    public function testGetCommunitiesSearchWhenCityWithoutCommunityNearByShouldReturnEmptyItemArray()
    {
        $this->performGetCommunitiesSearch(
            self::NON_EXIST_CITY,
            self::LOCATION_IN_BEIJING_LAT,
            self::LOCATION_IN_BEIJING_LNG
        );

        $this->assertResponseIsAnEmptyArray();
    }

    public function testGetCommunitiesSearchWithOneCommunityNearByShouldReturnOneItemArray()
    {
        $this->given('beijing');

        $this->performGetCommunitiesSearch(
            $this->entity->getId(),
            self::LOCATION_IN_BEIJING_LAT,
            self::LOCATION_IN_BEIJING_LNG
        );

        $this->assertResponseHasSpecificItemsAmountArray(1);
    }

    public function testGetCommunitiesSearchWithManyCommunitiesNearByShouldReturnManyItemsArray()
    {
        $this->given('shanghai');

        $this->performGetCommunitiesSearch(
            $this->entity->getId(),
            self::LOCATION_IN_SHANGHAI_LAT,
            self::LOCATION_IN_SHANGHAI_LNG
        );

        $this->assertResponseHasSpecificItemsAmountArray(3);
    }

    // conveniency methods

    private function performGetCommunitiesFilter()
    {
        $this->performGET('/location/communities/filter');
    }

    private function performGetCommunitiesSearch(
        $city,
        $lat,
        $lng
    ) {
        $this->performGET('/location/communities/search?city='.
            $city.
            '&lat='.$lat.
            '&lng='.$lng
        );
    }

    private function buildFilterData()
    {
        $this->given('first-room-type');
        $firstSpaceType = $this->entity;
        $this->given('second-room-type');
        $secondSpaceType = $this->entity;
        $this->given('third-room-type');
        $thirdSpaceType = $this->entity;
        $this->given('fourth-room-type');
        $fourthSpaceType = $this->entity;
        $this->given('fifth-room-type');
        $fifthSpaceType = $this->entity;
        $this->given('sixth-room-type');
        $sixthSpaceType = $this->entity;
        $this->given('seventh-room-type');
        $seventhSpaceType = $this->entity;

        $this->given('first-building-tag');
        $firstBuildingTag = $this->entity;
        $this->given('second-building-tag');
        $secondBuildingTag = $this->entity;

        $this->given('first-building-service');
        $firstBuildingService = $this->entity;
        $this->given('second-building-service');
        $secondBuildingService = $this->entity;

        return [
            [
                'name' => 'Type',
                'filters' => [
                    [
                        'type' => 'tag',
                        'name' => 'Type',
                        'queryParamKey' => 'room_types[]',
                        'filterAllTitle' => 'All Space',
                        'items' => [
                            [
                                'id' => $firstSpaceType->getId(),
                                'name' => $this->getContainer()->get('translator')
                                    ->trans(ProductOrderExport::TRANS_ROOM_TYPE.$firstSpaceType->getName()),
                            ],
                            [
                                'id' => $secondSpaceType->getId(),
                                'name' => $this->getContainer()->get('translator')
                                    ->trans(ProductOrderExport::TRANS_ROOM_TYPE.$secondSpaceType->getName()),
                            ],
                            [
                                'id' => $thirdSpaceType->getId(),
                                'name' => $this->getContainer()->get('translator')
                                    ->trans(ProductOrderExport::TRANS_ROOM_TYPE.$thirdSpaceType->getName()),
                            ],
                            [
                                'id' => $fourthSpaceType->getId(),
                                'name' => $this->getContainer()->get('translator')
                                    ->trans(ProductOrderExport::TRANS_ROOM_TYPE.$fourthSpaceType->getName()),
                            ],
                            [
                                'id' => $fifthSpaceType->getId(),
                                'name' => $this->getContainer()->get('translator')
                                    ->trans(ProductOrderExport::TRANS_ROOM_TYPE.$fifthSpaceType->getName()),
                            ],
                            [
                                'id' => $sixthSpaceType->getId(),
                                'name' => $this->getContainer()->get('translator')
                                    ->trans(ProductOrderExport::TRANS_ROOM_TYPE.$sixthSpaceType->getName()),
                            ],
                            [
                                'id' => $seventhSpaceType->getId(),
                                'name' => $this->getContainer()->get('translator')
                                    ->trans(ProductOrderExport::TRANS_ROOM_TYPE.$seventhSpaceType->getName()),
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Sort By',
                'filters' => [
                    [
                        'type' => 'radio',
                        'name' => 'Sort By',
                        'queryParamKey' => 'sort_by',
                        'items' => [
                            [
                                'name' => 'Distance',
                                'key' => 'distance',
                                'selected' => true,
                            ],
                            [
                                'name' => 'Stars',
                                'key' => 'star',
                                'selected' => false,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Filter',
                'filters' => [
                    [
                        'type' => 'tag',
                        'name' => 'Tag（Multiple Choices）:',
                        'queryParamKey' => 'building_tags[]',
                        'filterAllTitle' => 'All Space',
                        'items' => [
                            [
                                'id' => $firstBuildingTag->getId(),
                                'name' => $this->getContainer()->get('translator')
                                    ->trans(LocationConstants::TRANS_BUILDING_TAG.$firstBuildingTag->getKey()),
                            ],
                            [
                                'id' => $secondBuildingTag->getId(),
                                'name' => $this->getContainer()->get('translator')
                                    ->trans(LocationConstants::TRANS_BUILDING_TAG.$secondBuildingTag->getKey()),
                            ],
                        ],
                    ],
                    [
                        'type' => 'tag',
                        'name' => 'Configure（Multiple Choices）:',
                        'queryParamKey' => 'building_services[]',
                        'items' => [
                            [
                                'id' => $firstBuildingService->getId(),
                                'name' => $this->getContainer()->get('translator')
                                    ->trans(LocationConstants::TRANS_BUILDING_SERVICE.$firstBuildingService->getKey()),
                            ],
                            [
                                'id' => $secondBuildingService->getId(),
                                'name' => $this->getContainer()->get('translator')
                                    ->trans(LocationConstants::TRANS_BUILDING_SERVICE.$secondBuildingService->getKey()),
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    // asserts
}
