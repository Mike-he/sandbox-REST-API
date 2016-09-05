<?php

namespace Sandbox\ApiBundle\Controller\Menu;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Sandbox\ApiBundle\Entity\Menu\Menu;

/**
 * Menu Controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimozh@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class MenuController extends SandboxRestController
{
    const CLIENT_MENU_ORDER = 'client.menu.order';
    const CLIENT_MENU_COFFEE = 'client.menu.coffee';
    const CLIENT_MENU_EVENT = 'client.menu.event';
    const CLIENT_MENU_LOCATION = 'client.menu.location';
    const CLIENT_MENU_COMMUNITY = 'client.menu.community';
    const CLIENT_MENU_BLOG = 'client.menu.blog';
    const CLIENT_MENU_MESSAGE = 'client.menu.message';
    const CLIENT_MENU_CONTACT = 'client.menu.contact';
    const CLIENT_MENU_MEMBER = 'client.menu.member';
    const CLIENT_MENU_COMPANY = 'client.menu.company';
    const CLIENT_MENU_MY_COMPANY = 'client.menu.my_company';
    const CLIENT_MENU_BALANCE = 'client.menu.balance';
    const CLIENT_MENU_MY_ORDER = 'client.menu.my_order';
    const CLIENT_MENU_MY_ROOM = 'client.menu.my_room';
    const CLIENT_MENU_NOTIFICATION = 'client.menu.notification';
    const CLIENT_MENU_MY_INVOICE = 'client.menu.my_invoice';
    const CLIENT_MENU_MEMBERSHIP_CARD = 'client.menu.membership_card';
    const CLIENT_MENU_RESET_PASSWORD = 'client.menu.reset_password';
    const CLIENT_MENU_EMAIL = 'client.menu.email';
    const CLIENT_MENU_PHONE = 'client.menu.phone';
    const CLIENT_MENU_ABOUT_US = 'client.menu.about_us';
    const CLIENT_MENU_SETTING = 'client.menu.setting';
    const ROOM_TYPE = 'room.type.';

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="component",
     *    nullable=false,
     *    requirements="(client|admin)",
     *    strict=true,
     *    description="The value of component"
     * )
     *
     * @Annotations\QueryParam(
     *    name="platform",
     *    nullable=false,
     *    requirements="(iphone|android)",
     *    strict=true,
     *    description="The value of platform"
     * )
     *
     * @Annotations\QueryParam(
     *    name="version",
     *    nullable=false,
     *    description="The value of version"
     * )
     *
     * @Annotations\QueryParam(
     *    name="position",
     *    default="main",
     *    nullable=false,
     *    description="The value of position"
     * )
     *
     * @Route("/menus")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMenuBarAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $etag = $request->headers->get('etag');

        $component = $paramFetcher->get('component');
        $platform = $paramFetcher->get('platform');
        $version = $paramFetcher->get('version');
        $position = $paramFetcher->get('position');

        $menuVersions = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Menu\Menu')
            ->getAllMenuVersion(
                $component,
                $platform
            );

        foreach ($menuVersions as $menuVersion) {
            $minVersion = $menuVersion['minVersion'];
            $maxVersion = $menuVersion['maxVersion'];

            if (
                version_compare($minVersion, $version, '<=') &&
                version_compare($maxVersion, $version, '>=')
            ) {
                $menuId = $menuVersion['id'];
                break;
            }
        }

        if (!isset($menuId) || is_null($menuId)) {
            return new View();
        }

        $menu = $this->getDoctrine()->getRepository('SandboxApiBundle:Menu\Menu')->find($menuId);

        $items = array(
            self::CLIENT_MENU_ORDER,
            self::CLIENT_MENU_COFFEE,
            self::CLIENT_MENU_EVENT,
            self::CLIENT_MENU_LOCATION,
            self::CLIENT_MENU_COMMUNITY,
            self::CLIENT_MENU_BLOG,
            self::CLIENT_MENU_MESSAGE,
            self::CLIENT_MENU_CONTACT,
            self::CLIENT_MENU_MEMBER,
            self::CLIENT_MENU_COMPANY,
            self::CLIENT_MENU_MY_COMPANY,
            self::CLIENT_MENU_BALANCE,
            self::CLIENT_MENU_MY_ORDER,
            self::CLIENT_MENU_MY_ROOM,
            self::CLIENT_MENU_NOTIFICATION,
            self::CLIENT_MENU_MY_INVOICE,
            self::CLIENT_MENU_MEMBERSHIP_CARD,
            self::CLIENT_MENU_RESET_PASSWORD,
            self::CLIENT_MENU_EMAIL,
            self::CLIENT_MENU_PHONE,
            self::CLIENT_MENU_ABOUT_US,
            self::CLIENT_MENU_SETTING,
        );

        // translate json
        switch ($position) {
            case Menu::POSITION_MAIN:
                $menuJson = $menu->getMainJson();
                break;
            case Menu::POSITION_PROFILE:
                $menuJson = $menu->getProfileJson();
                break;
            case Menu::POSITION_HOME:
                $menuJson = $this->generateHomeJson($menu->getHomeJson());
                break;
            default:
                return new View();
        }

        foreach ($items as $item) {
            $translate = $this->get('translator')->trans($item);
            $menuJson = preg_replace('/'.$item.'/', "$translate", $menuJson);
        }

        $view = new View();

        $menuHash = hash('sha256', $menuJson);
        $view->setHeader('etag', $menuHash);

        // check hash
        if ($etag == $menuHash) {
            return $view;
        }

        $view->setData(json_decode($menuJson, true));

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="target",
     *    nullable=false,
     *    strict=true,
     *    description=""
     * )
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description=""
     * )
     *
     * @Annotations\QueryParam(
     *    name="offset",
     *    array=false,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description=""
     * )
     *
     * @Route("/menus/loadmore")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getLoadMoreAction(
       Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $target = $paramFetcher->get('target');
        $limit = $paramFetcher->get('limit');
        $offset = ($paramFetcher->get('offset') - 1) * $limit;

        $items = $this->handleBanner($target, $limit, $offset);

        $view = new View();
        $view->setData($items);

        return $view;
    }

    /**
     * @param $menuJson
     *
     * @return string
     */
    private function generateHomeJson(
        $menuJson
    ) {
        $menuArray = json_decode($menuJson, true);
        $bannerCarouselMenu = array();
        $iconsMenu = array();
        $bannerMenu = array();
        foreach ($menuArray as $menu) {
            switch ($menu['type']) {
                case 'bannerCarousel':
                    $items = $menu['items'];
                    if (!empty($menu['hidden_asserts'])) {
                        $items = $this->handleBannerCarousel($items, $menu['hidden_asserts']);
                    }
                    $bannerCarouselMenu = array(
                        'type' => 'bannerCarousel',
                        'items' => $items,
                    );
                    break;
                case 'icons':
                    $items = array();
                    if (!empty($menu['hidden_asserts'])) {
                        $items = $this->handleIcons($items, $menu['hidden_asserts']);
                    }
                    $iconsMenu = array(
                        'type' => 'icons',
                        'items' => $items,
                    );
                    break;
                case 'banner':
                    if (!empty($menu['hidden_asserts'])) {
                        foreach ($menu['hidden_asserts'] as $assert) {
                            $item_key = $assert['item_key'];
                            $limit = $assert['limit'];
                            $offset = ($assert['offset'] - 1) * $limit;
                            $bannerMenu = $this->handleBanner($item_key, $limit, $offset);
                        }
                    }
                    break;
                default;
            }
        }
        $newMenuArray = array($bannerCarouselMenu, $iconsMenu, $bannerMenu);

        return json_encode($newMenuArray);
    }

    /**
     * @param $items
     * @param $asserts
     *
     * @return array
     */
    private function handleBannerCarousel(
        $items,
        $asserts
    ) {
        foreach ($asserts as $assert) {
            $item_key = $assert['item_key'];
            $limit = $assert['limit'];
            $offset = ($assert['offset'] - 1) * $limit;
            switch ($item_key) {
                case 'banner':
                    $data = $this->getDoctrine()->getRepository("SandboxApiBundle:Banner\Banner")->getLimitList($limit, $offset);
                    $bannerItem = array();
                    foreach ($data as $d) {
                        if ($d->getSource() == 'url') {
                            $url = $d->getContent();
                        } else {
                            $url = $this->container->getParameter('mobile_url').'/'.$d->getSource().'?ptype=detail&id='.$d->getSourceId();
                        }
                        $bannerItem[] = array(
                            'type' => 'web',
                            'title' => $d->getTitle(),
                            'image_url' => $d->getCover(),
                            'web' => array(
                                'url' => $url,
                                'cookie' => array(
                                    'key' => 'btype',
                                    'value' => 'bannerCarousel',
                                ),
                            ),
                        );
                    }
                    $newItems = array_merge_recursive($items, $bannerItem);
                    break;
            }
        }

        return $newItems;
    }

    /**
     * @param $items
     * @param $asserts
     *
     * @return array
     */
    private function handleIcons(
        $items,
        $asserts
    ) {
        foreach ($asserts as $assert) {
            $item_key = $assert['item_key'];
            $limit = $assert['limit'];
            $offset = ($assert['offset'] - 1) * $limit;
            switch ($item_key) {
                case 'room_types':
                    $roomTypeItem = array();
                    $data = $this->getDoctrine()->getRepository("SandboxApiBundle:Room\RoomTypes")->getLimitList($limit, $offset);
                    foreach ($data as $d) {
                        $roomTypeItem[] = array(
                            'type' => 'web',
                            'name' => $this->get('translator')->trans(self::ROOM_TYPE.$d->getName()),
                            'icon_url' => $d->getIcon(),
                            'web' => array(
                                'url' => $this->container->getParameter('mobile_url').'/search',
                                'cookie' => array(
                                    'key' => 'btype',
                                    'value' => $d->getName(),
                                ),
                            ),
                        );
                    }
                    $items = array_merge_recursive($items, $roomTypeItem);
                    break;
            }
        }

        return $items;
    }

    /**
     * @param $key
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    private function handleBanner(
        $key,
        $limit,
        $offset
    ) {
        $bannerMenu = array();
        switch ($key) {
            case 'banner':
                $data = $this->getDoctrine()->getRepository("SandboxApiBundle:Banner\Banner")->getLimitList($limit, $offset);
                foreach ($data as $d) {
                    if ($d->getSource() == 'url') {
                        $url = $d->getContent();
                    } else {
                        $url = $this->container->getParameter('mobile_url').'/'.$d->getSource().'?ptype=detail&id='.$d->getSourceId();
                    }
                    $bannerMenu[] = array(
                        'type' => 'banner',
                        'item' => array(
                            'type' => 'web',
                            'title' => $d->getTitle(),
                            'subtitle' => $d->getSubtitle(),
                            'tag' => $this->get('translator')->trans($d->getTag()->getKey()),
                            'image_url' => $d->getCover(),
                            'web' => array(
                                'url' => $url,
                                'cookie' => array(
                                    'key' => 'btype',
                                    'value' => 'banner',
                                ),
                            ),
                        ),
                    );
                }
                break;
        }

        return $bannerMenu;
    }

//    /**
//     * List menus.
//     *
//     * @param Request               $request
//     * @param ParamFetcherInterface $paramFetcher param fetcher service
//     *
//     *  @ApiDoc(
//     *   resource = true,
//     *   statusCodes = {
//     *     200 = "Returned when successful"
//     *   }
//     * )
//     *
//     * @Annotations\QueryParam(
//     *    name="component",
//     *    nullable=false,
//     *    requirements="(client|admin)",
//     *    strict=true,
//     *    description="The value of component"
//     * )
//     *
//     * @Annotations\QueryParam(
//     *    name="platform",
//     *    nullable=false,
//     *    requirements="(iphone|android)",
//     *    strict=true,
//     *    description="The value of platform"
//     * )
//     *
//     * @Annotations\QueryParam(
//     *    name="version",
//     *    nullable=false,
//     *    description="The value of version"
//     * )
//     *
//     * @Method({"GET"})
//     * @Route("/menus")
//     *
//     * @return View
//     *
//     * @throws \Exception
//     */
//    public function getMenusAction(
//        Request $request,
//        ParamFetcherInterface $paramFetcher
//    ) {
//        $component = $paramFetcher->get('component');
//        $platform = $paramFetcher->get('platform');
//        $version = $paramFetcher->get('version');
//
//        $menus = $this->getRepo('Menu\Menu')->findBy(
//            array(
//                'component' => $component,
//                'platform' => $platform,
//                'version' => $version,
//            ),
//            array('section' => 'ASC')
//        );
//
//        $menuResponse = array();
//
//        if ($component === Menu::COMPONENT_CLIENT) {
//            $leftMenuArray = array();
//            $rightMenuArray = array();
//
//            foreach ($menus as $menu) {
//                if ($menu->getPosition() === Menu::POSITION_LEFT) {
//                    array_push($leftMenuArray, $menu);
//                } elseif ($menu->getPosition() === Menu::POSITION_RIGHT) {
//                    array_push($rightMenuArray, $menu);
//                }
//            }
//
//            $menuResponse['left_menus'] = $this->setClientMenus($leftMenuArray);
//            $menuResponse['right_menus'] = $this->setClientMenus($rightMenuArray);
//        }
//
//        return new View($menuResponse);
//    }
//
//    /**
//     * @param array $menus
//     *
//     * @return array
//     */
//    private function setClientMenus(
//        $menus
//    ) {
//        $menuArray = array();
//
//        foreach ($menus as $menu) {
//            $sectionStr = strval($menu->getSection());
//            $partIdx = $menu->getPart() - 1;
//            $numberIdx = $menu->getNumber() - 1;
//
//            $menuArray[$sectionStr][$partIdx][$numberIdx] = array(
//                'key' => $menu->getKey(),
//                'type' => $menu->getType(),
//                'name' => '',
//                'url' => $menu->getUrl(),
//                'ready' => $menu->isReady(),
//            );
//        }
//
//        return $menuArray;
//    }
}
