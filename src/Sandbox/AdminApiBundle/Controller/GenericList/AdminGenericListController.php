<?php

namespace Sandbox\AdminApiBundle\Controller\GenericList;

use FOS\RestBundle\View\View;
use Sandbox\AdminApiBundle\Controller\AdminRestController;
use Sandbox\ApiBundle\Entity\GenericList\GenericUserList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;

class AdminGenericListController extends AdminRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="object",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    description="object name"
     * )
     *
     * @Route("/generic/lists")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getGenericListsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $platform = $adminPlatform['platform'];

        $object = $paramFetcher->get('object');

        $lists = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:GenericList\GenericList')
            ->findBy(
              array(
                  'object' => $object,
                  'platform' => $platform,
              )
            );

        return new View($lists);
    }

    /**
     * create admin remarks.
     *
     * @param Request $request the request object
     *
     * @Method({"POST"})
     * @Route("/generic/lists")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function createUserListAction(
        Request $request
    ) {
        $userId = $this->getAdminId();
        $em = $this->getDoctrine()->getManager();

        $payload = json_decode($request->getContent(), true);

        $listIds = $payload['list_ids'];
        $object = $payload['object'];

        $oldLists = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:GenericList\GenericUserList')
            ->findBy(
                array(
                    'userId' => $userId,
                    'object' => $object,
                )
            );

        if ($oldLists) {
            foreach ($oldLists as $oldList) {
                $em->remove($oldList);
            }

            $em->flush();
        }

        foreach ($listIds as $listId) {
            $list = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:GenericList\GenericList')
                ->findOneBy(array(
                    'id' => $listId,
                    'object' => $object,
                ));

            if ($list) {
                $userList = new GenericUserList();
                $userList->setUserId($userId);
                $userList->setList($list);
                $userList->setObject($object);

                $em->persist($userList);
            }
        }

        $em->flush();

        $view = new View();
        $view->setStatusCode(201);

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="object",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    description="object name"
     * )
     *
     * @Route("/generic/lists/user")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getUserGenericLists(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getAdminId();
        $object = $paramFetcher->get('object');

        $lists = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:GenericList\GenericUserList')
            ->findBy(
                array(
                    'object' => $object,
                    'userId' => $userId,
                )
            );

        $result = array();
        if ($lists) {
            foreach ($lists as $list) {
                $result[] = $list->getList();
            }
        } else {
            $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
            $platform = $adminPlatform['platform'];

            $result = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:GenericList\GenericList')
                ->findBy(
                    array(
                        'object' => $object,
                        'platform' => $platform,
                        'default' => true,
                    )
                );
        }

        return new View($result);
    }
}
