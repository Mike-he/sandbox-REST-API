<?php

namespace Sandbox\CommnueAdminApiBundle\Controller\Advertising;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\AdminApiBundle\Data\Banner\BannerPosition;
use Sandbox\ApiBundle\Controller\Advertising\AdvertisingController;
use Sandbox\ApiBundle\Entity\Banner\Banner;
use Sandbox\ApiBundle\Entity\Banner\CommnueBanner;
use Sandbox\ApiBundle\Entity\Material\CommnueMaterial;
use Sandbox\ApiBundle\Form\Advertising\AdvertisingPositionType;
use Sandbox\ApiBundle\Form\Banner\CommnueBannerPatchType;
use Sandbox\ApiBundle\Form\Banner\CommnueBannerType;
use Sandbox\CommnueAdminApiBundle\Data\Advertising\AdvertisingPosition;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use Knp\Component\Pager\Paginator;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Rs\Json\Patch;

class AdminAdvertisingScreenController extends AdvertisingController
{
    public function postScreenAdvertisingAction()
    {

    }
}