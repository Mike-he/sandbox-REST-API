<?php

namespace Sandbox\ApiBundle\Tests\Controller\User;

use AllanSimon\TestHelpers\ApiHelpersTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Sandbox\ApiBundle\Tests\Traits\CommonTestsUtilsTrait;
use Sandbox\ApiBundle\Traits\HandleCoordinateTrait;

class ClientUserAppointmentProfileControllerTest extends WebTestCase
{
    use ApiHelpersTrait;
    use CommonTestsUtilsTrait;
    use HandleCoordinateTrait;

    public function setUp()
    {
        $this->client = static::createClient();

        $fixtures = [
            'Sandbox\ApiBundle\DataFixtures\ORM\Sales\LoadSalesCompanyData',
            'Sandbox\ApiBundle\DataFixtures\ORM\User\LoadUserData',
            'Sandbox\ApiBundle\DataFixtures\ORM\User\LoadUserAppointmentProfileData',
        ];

        $fixtureExecutor = $this->loadFixtures($fixtures);
        $this->fixtures = $fixtureExecutor->getReferenceRepository();
        $this->em = $fixtureExecutor->getObjectManager();
    }

    public function testGetUserAppointmentProfilesWithoutAuthenticationShouldNotWork()
    {
        $this->performGetClientAppointmentProfiles();

        $this->assertPermissionDenied();
    }

    public function testGetUserAppointmentProfilesWithAuthenticationShouldWork()
    {
        $this->givenLoggedInAs('client-3', 'user-token-3');

        $this->performGetClientAppointmentProfiles();

        $this->assertOkSuccess();
    }

    public function testGetUserAppointmentProfileByIdWithoutAuthenticationShouldNotWork()
    {
        $this->given('user-appointment-profile-2');
        $profileId = $this->entity->getId();

        $this->performGetClientAppointmentProfileById($profileId);

        $this->assertPermissionDenied();
    }

    public function testGetUserAppointmentProfileByIdWithAuthenticationShouldWork()
    {
        $this->givenLoggedInAs('client-3', 'user-token-3');

        $this->given('user-appointment-profile-2');
        $profileId = $this->entity->getId();

        $this->performGetClientAppointmentProfileById($profileId);

        $this->assertOkSuccess();
    }

    public function testPostUserAppointmentProfileWithoutAuthenticationShouldNotWork()
    {
        $data = $this->constructClientAppointmentProfileData();

        $this->performPostClientAppointmentProfiles($data);

        $this->assertPermissionDenied();
    }

    public function testPostUserAppointmentProfileWithAuthenticationShouldWork()
    {
        $this->givenLoggedInAs('client-3', 'user-token-3');

        $postAmount = $this->getCurrentAmountInDatabase('User\UserAppointmentProfile');

        $data = $this->constructClientAppointmentProfileData();

        $this->performPostClientAppointmentProfiles($data);

        $this->assertCreatedSuccess();

        $this->assertEquals(
            $postAmount + 1,
            $this->getCurrentAmountInDatabase('User\UserAppointmentProfile'),
            'The posts amount in database is incorrect.'
        );
    }

    public function testPutUserAppointmentProfileWithoutAuthenticationShouldNotWork()
    {
        $data = $this->constructClientAppointmentProfileData();

        $this->given('user-appointment-profile-2');
        $profileId = $this->entity->getId();

        $this->performPutClientAppointmentProfiles($profileId, $data);

        $this->assertPermissionDenied();
    }

    public function testPutUserAppointmentProfileWithAuthenticationShouldWork()
    {
        $this->givenLoggedInAs('client-3', 'user-token-3');

        $data = $this->constructClientAppointmentProfileData();

        $this->given('user-appointment-profile-2');
        $profileId = $this->entity->getId();

        $this->performPutClientAppointmentProfiles($profileId, $data);

        $this->assertNoContentResponse();
    }

    public function testDeleteUserAppointmentProfileWithoutAuthenticationShouldNotWork()
    {
        $this->given('user-appointment-profile-3');
        $profileId = $this->entity->getId();

        $this->performDeleteClientAppointmentProfiles($profileId);

        $this->assertPermissionDenied();
    }

    public function testDeleteUserAppointmentProfileWithAuthenticationShouldWork()
    {
        $this->givenLoggedInAs('client-3', 'user-token-3');

        $this->given('user-appointment-profile-3');
        $profileId = $this->entity->getId();

        $this->performDeleteClientAppointmentProfiles($profileId);

        $this->assertNoContentResponse();
    }

    private function performGetClientAppointmentProfiles()
    {
        $this->performGET('/client/user/appointment/profiles');
    }

    private function performGetClientAppointmentProfileById($id)
    {
        $this->performGET('/client/user/appointment/profiles/'.$id);
    }

    private function performPostClientAppointmentProfiles(
        $data
    ) {
        $this->performPOST('/client/user/appointment/profiles', $data);
    }

    private function performPutClientAppointmentProfiles(
        $id,
        $data
    ) {
        $this->performPUT('/client/user/appointment/profiles/'.$id, $data);
    }

    private function performDeleteClientAppointmentProfiles(
        $id
    ) {
        $this->performDELETE('/client/user/appointment/profiles/'.$id);
    }

    private function constructClientAppointmentProfileData()
    {
        return array(
            'name' => 'test company name',
            'contact' => 'test contact person',
            'email' => 'test@gmail.com',
            'phone' => '4125554449',
            'address' => 'test address',
        );
    }
}
