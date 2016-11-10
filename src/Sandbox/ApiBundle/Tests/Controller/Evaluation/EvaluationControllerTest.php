<?php

namespace Sandbox\ApiBundle\Tests\Controller;

use AllanSimon\TestHelpers\ApiHelpersTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Sandbox\ApiBundle\Constants\LocationConstants;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Tests\Traits\CommonTestsUtilsTrait;
use Sandbox\ApiBundle\Traits\HandleCoordinateTrait;

class EvaluationControllerTest extends WebTestCase
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
            'Sandbox\ApiBundle\DataFixtures\ORM\Location\LoadLocationData',
            'Sandbox\ApiBundle\DataFixtures\ORM\User\LoadUserData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Evaluation\LoadEvaluationData',
        ];

        $fixtureExecutor = $this->loadFixtures($fixtures);
        $this->fixtures = $fixtureExecutor->getReferenceRepository();
        $this->em = $fixtureExecutor->getObjectManager();
    }

    /**
     * Get Client Evaluations Without Authentication Should Work
     */
    public function testGetClientEvaluationsWithoutAuthenticationShouldWork()
    {
        $this->given('room-building-for-data-structure');
        $buildingId = $this->entity->getId();

        $this->performGetClientEvaluations($buildingId);

        $this->assertOkSuccess();
    }

    /**
     * Get Client Evaluations Should Return Correct Fields Amount
     */
    public function testGetClientEvaluationsShouldReturnCorrectFieldsAmount()
    {
        $this->given('room-building-for-data-structure');
        $buildingId = $this->entity->getId();

        $this->performGetClientEvaluations($buildingId);

        $this->assertResponseContainsCorrectFieldsAmount(
            self::EVALUATION_AMOUNT
        );
    }

    public function testGetClientEvaluationShouldReturnCorrectDataStructure()
    {
        $this->given('room-building-for-data-structure');
        $buildingId = $this->entity->getId();

        $this->performGetClientEvaluations($buildingId);

        $data = $this->buildFilterData();

        $this->assertResponseContainsCorrectDataFields($data);
    }

    public function testGetMyClientEvaluationWithAuthenticationShouldWork()
    {
        $this->performGetMyEvaluation();

        $this->assertOkSuccess();
    }

    // conveniency methods

    private function performGetClientEvaluations(
        $buildingId
    ) {
        $this->performGET('/client/evaluations?building='.$buildingId);
    }

    private function performGetMyEvaluation() {
        $this->performGET('/client/evaluations/my');
    }

    private function buildFilterData()
    {
        $this->given('evaluation-with_comment-with_pic');
        $firstEvaluation = $this->entity;
        $this->given('evaluation-no1-attachment');
        $firstEvaluationAttachment = $this->entity;
        $this->given('user-mike');
        $user = $this->entity;
        $this->given('user-profile-mike');
        $userProfile = $this->entity;

        $this->given('evaluation-no_comment-with_pic');
        $secondEvaluation = $this->entity;
        $this->given('evaluation-no2-attachment');
        $secondEvaluationAttachment = $this->entity;

        $this->given('evaluation-with_comment-no_pic');
        $thirdEvaluation = $this->entity;

        return [
            [
                'id' => $firstEvaluation->getId(),
                'type' => $firstEvaluation->getType(),
                'total' => $firstEvaluation->getTotalStar(),
                'comment' => $firstEvaluation->getComment(),
                'user' => [
                    'id' => $user->getId(),
                    'name' => $userProfile->getName(),
                ],
                'evaluation_attachments' => [
                    [
                        'content' => $firstEvaluationAttachment->getContent(),
                        'attachment_type' => $firstEvaluationAttachment->getAttachmentType(),
                        'filename' => $firstEvaluationAttachment->getFilename(),
                        'size' => $firstEvaluationAttachment->getSize(),
                    ],
                ],
                'creation_date' => $firstEvaluation->getCreationDate(),
            ],
            [
                'id' => $secondEvaluation->getId(),
                'type' => $secondEvaluation->getType(),
                'total' => $secondEvaluation->getTotalStar(),
                'user' => [
                    'id' => $user->getId(),
                    'name' => $userProfile->getName(),
                ],
                'evaluation_attachments' => [
                    [
                        'content' => $secondEvaluationAttachment->getContent(),
                        'attachment_type' => $secondEvaluationAttachment->getAttachmentType(),
                        'filename' => $secondEvaluationAttachment->getFilename(),
                        'size' => $secondEvaluationAttachment->getSize(),
                    ],
                ],
                'creation_date' => $secondEvaluation->getCreationDate(),
            ],
            [
                'id' => $thirdEvaluation->getId(),
                'type' => $thirdEvaluation->getType(),
                'total' => $thirdEvaluation->getTotalStar(),
                'comment' => $thirdEvaluation->getComment(),
                'user' => [
                    'id' => $user->getId(),
                    'name' => $userProfile->getName(),
                ],
                'evaluation_attachments' => [],
                'creation_date' => $thirdEvaluation->getCreationDate(),
            ],
        ];

        // asserts
    }
}
