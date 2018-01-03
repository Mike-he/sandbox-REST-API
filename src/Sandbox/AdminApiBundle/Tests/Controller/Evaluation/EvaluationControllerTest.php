<?php

namespace Sandbox\AdminApiBundle\Tests\Controller\Evaluation;

use AllanSimon\TestHelpers\ApiHelpersTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Sandbox\ApiBundle\Tests\Traits\CommonTestsUtilsTrait;

class EvaluationControllerTest extends WebTestCase
{
    use ApiHelpersTrait;
    use CommonTestsUtilsTrait;

    public function setUp()
    {
        $this->client = static::createClient();

        $fixtures = [
            'Sandbox\ApiBundle\DataFixtures\ORM\User\LoadUserData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Sales\LoadSalesCompanyData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Admin\LoadAdminPositionData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Admin\LoadAdminPositionBindingData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Room\LoadRoomCityData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Room\LoadRoomBuildingData',
            'Sandbox\ApiBundle\DataFixtures\ORM\Evaluation\LoadEvaluationData',
        ];

        $fixtureExecutor = $this->loadFixtures($fixtures);
        $this->fixtures = $fixtureExecutor->getReferenceRepository();
        $this->em = $fixtureExecutor->getObjectManager();
    }

    /**
     * Post Official Evaliation Without Authentication Should Not Work.
     */
    public function testPostOfficialEvaluationWithoutAuthenticationShouldNotWork()
    {
        $data = $this->constructOfficialEvaluationData();

        $this->performPostOfficialEvaluation($data);

        $this->assertPermissionDenied();
    }

    /**
     * Post Official Evaliation Without Permission Should Not Work.
     */
    public function testPostOfficialEvaluationWithoutPermissionShouldNotWork()
    {
        $this->givenLoggedInAs('client-2', 'user-token-2');

        $data = $this->constructOfficialEvaluationData();

        $this->performPostOfficialEvaluation($data);

        $this->assertPermissionDenied();
    }

    /**
     * Post Official Evaliation With Permission Should Work.
     */
    public function testPostOfficialEvaluationWithPermissionShouldWork()
    {
        $this->givenLoggedInAs('client-mike', 'user-token-mike');

        $postAmount = $this->getCurrentAmountInDatabase('Evaluation\Evaluation');

        $data = $this->constructOfficialEvaluationData();

        $this->performPostOfficialEvaluation($data);

        $this->assertNoContentResponse();

        $this->assertEquals(
            $postAmount + 1,
            $this->getCurrentAmountInDatabase('Evaluation\Evaluation'),
            'The posts amount in database is incorrect.'
        );
    }

    /**
     * Patch Evaliation Visible Without Authentication Should Not Work.
     */
    public function testPatchEvaluationWithoutAuthenticationShouldNotWork()
    {
        $this->given('evaluation-with_comment-with_pic');

        $data = $this->constructPatchEvaluationVisibleData();

        $this->performPatchEvaluation($this->entity->getId(), $data);

        $this->assertPermissionDenied();
    }

    /**
     * Patch Evaliation Visible Without Permission Should Not Work.
     */
    public function testPatchEvaluationWithoutPermissionShouldNotWork()
    {
        $this->givenLoggedInAs('client-2', 'user-token-2');

        $this->given('evaluation-with_comment-with_pic');

        $data = $this->constructPatchEvaluationVisibleData();

        $this->performPatchEvaluation($this->entity->getId(), $data);

        $this->assertPermissionDenied();
    }

    /**
     * Patch Evaliation Visible With Permission Should Work.
     */
    public function testPatchEvaluationWithPermissionShouldWork()
    {
        $this->givenLoggedInAs('client-mike', 'user-token-mike');

        $this->given('evaluation-with_comment-with_pic');

        $data = $this->constructPatchEvaluationVisibleData();

        $this->performPatchEvaluation($this->entity->getId(), $data);

        $this->assertNoContentResponse();
    }

    // conveniency methods

    private function performPostOfficialEvaluation(
        $data
    ) {
        $this->performPost('/admin/evaluation/official', $data);
    }

    private function performPatchEvaluation(
        $id,
        $data
    ) {
        $this->performPATCH('/admin/evaluation/'.$id, $data);
    }

    private function constructOfficialEvaluationData()
    {
        $this->given('room-building-for-data-structure');
        $building = $this->entity;

        $data = array(
            'official_evaluation_star' => '4',
            'building_id' => $building->getId(),
        );

        return $data;
    }

    private function constructPatchEvaluationVisibleData()
    {
        $data = array(
            array(
                'op' => 'add',
                'path' => '/visible',
                'value' => false,
            ),
        );

        return $data;
    }
}
