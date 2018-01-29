<?php

namespace Sandbox\ClientPropertyApiBundle\Controller\ChatGroup;

use Sandbox\AdminApiBundle\Command\SyncJmessageUserCommand;
use Sandbox\ApiBundle\Controller\ChatGroup\ChatGroupController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;

class ClientChatGroupMemberController extends ChatGroupController
{
    /**
     * @param Request $request
     * @param $userId
     *
     * @Route("/chatgroups/creator/{userId}")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function UpdateCreatorInfoAction(
        Request $request,
        $userId
    ) {
        //execute SyncJmessageUserCommand
        $command = new SyncJmessageUserCommand();
        $command->setContainer($this->container);

        $input = new ArrayInput(array('userId' => $userId));
        $output = new NullOutput();

        $command->run($input, $output);

        $view = new View();

        return $view;
    }
}
