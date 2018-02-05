<?php

namespace Sandbox\CommnueAdminApiBundle\Controller\Expert;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;
use Sandbox\ApiBundle\Constants\PlatformConstants;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminRemark;
use Sandbox\ApiBundle\Entity\Expert\Expert;
use Sandbox\ApiBundle\Form\Expert\ExpertPatchType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class AdminExpertController extends SandboxRestController
{
    const MESSAGE_SUCCESS = '恭喜您，专家身份审核成功。';
    const MESSAGE_FAILURE = '您的专家身份审核未通过，请修改资料后重新提交。';

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="banned",
     *     array=false,
     *     nullable=true,
     *     default=null,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="name",
     *     array=false,
     *     nullable=true,
     *     default=null,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="status",
     *     array=false,
     *     nullable=true,
     *     default=null,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="phone",
     *     array=false,
     *     nullable=true,
     *     default=null,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true
     * )
     *
     * @Route("/experts")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getExpertsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $banned = (bool) $paramFetcher->get('banned');
        $name = $paramFetcher->get('name');
        $phone = $paramFetcher->get('phone');
        $status = $paramFetcher->get('status');
        $pageIndex = $paramFetcher->get('pageIndex');
        $pageLimit = $paramFetcher->get('pageLimit');

        $limit = $pageLimit;
        $offset = ($pageIndex - 1) * $pageLimit;

        $experts = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')
            ->getAdminExperts(
                $banned,
                $name,
                $phone,
                $status,
                $limit,
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')
            ->countAdminExperts(
                $banned,
                $name,
                $phone,
                $status
            );

        foreach ($experts as $expert) {
            $this->setExpertLocation($expert);
        }

        $view = new View();
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $experts,
                'total_count' => (int) $count,
            )
        );

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     *
     * @Route("/experts/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getExpertAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $expert = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')
            ->find($id);

        $this->setExpertLocation($expert);

        return new View($expert);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     *
     * @Route("/experts/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchExpertAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $expert = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')
            ->find($id);
        $this->throwNotFoundIfNull($expert, self::NOT_FOUND_MESSAGE);

        $oldStatus = $expert->getStatus();
        $message = null;

        $expertJson = $this->container->get('serializer')->serialize($expert, 'json');
        $patch = new Patch($expertJson, $request->getContent());
        $expertJson = $patch->apply();

        $form = $this->createForm(new ExpertPatchType(), $expert);
        $form->submit(json_decode($expertJson, true));

        $newStatus = $expert->getStatus();
        if ($oldStatus != $newStatus) {
            switch ($newStatus) {
                case Expert::STATUS_SUCCESS:
                    $message = self::MESSAGE_SUCCESS;
                    break;
                case Expert::STATUS_FAILURE:

                    if (is_null($expert->getRemark())) {
                        return $this->customErrorView(
                            400,
                            CustomErrorMessagesConstants::ERROR_PAYLOAD_FORMAT_NOT_CORRECT_CODE,
                            CustomErrorMessagesConstants::ERROR_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE
                        );
                    }

                    $this->get('sandbox_api.admin_remark')->autoRemark(
                        $this->getAdminId(),
                        PlatformConstants::PLATFORM_COMMNUE,
                        null,
                        $expert->getRemark(),
                        AdminRemark::OBJECT_EXPERT,
                        $id
                    );

                    $message = self::MESSAGE_FAILURE;

                    break;
                default:
                    return $this->customErrorView(
                        400,
                        CustomErrorMessagesConstants::ERROR_PAYLOAD_FORMAT_NOT_CORRECT_CODE,
                        CustomErrorMessagesConstants::ERROR_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE
                    );
            }
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        if ($message) {
            //todo: Jpush message
        }

        return new View();
    }

    /**
     * @param Expert $expert
     */
    private function setExpertLocation(
        $expert
    ) {
        $countryId = $expert->getCountryId();
        $provinceId = $expert->getProvinceId();
        $cityId = $expert->getCityId();
        $districtId = $expert->getDistrictId();

        $location = '';
        if (!is_null($countryId)) {
            $country = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomCity')
                ->find($countryId);

            $location .= $country->getName();
        }

        if (!is_null($provinceId)) {
            $province = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomCity')
                ->find($provinceId);

            $location .= $province->getName();
        }

        if (!is_null($cityId)) {
            $city = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomCity')
                ->find($cityId);

            $location .= $city->getName();
        }

        if (!is_null($districtId)) {
            $district = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomCity')
                ->find($districtId);

            $location .= $district->getName();
        }

        $expert->setLocation($location);
    }
}
