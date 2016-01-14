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
 * Feature Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class MenuController extends SandboxRestController
{
    /**
     * List menus.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     *  @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
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
     * @Method({"GET"})
     * @Route("/menus")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getMenusAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $component = $paramFetcher->get('component');
        $platform = $paramFetcher->get('platform');
        $version = $paramFetcher->get('version');

        $menus = $this->getRepo('Menu\Menu')->findBy(
            array(
                'component' => $component,
                'platform' => $platform,
                'version' => $version,
            ),
            array('section' => 'ASC')
        );

        $menuResponse = array();

        if ($component === Menu::COMPONENT_CLIENT) {
            $leftMenuArray = array();
            $rightMenuArray = array();

            foreach ($menus as $menu) {
                if ($menu->getPosition() === Menu::POSITION_LEFT) {
                    array_push($leftMenuArray, $menu);
                } elseif ($menu->getPosition() === Menu::POSITION_RIGHT) {
                    array_push($rightMenuArray, $menu);
                }
            }

            $menuResponse['left_menus'] = $this->setClientMenus($leftMenuArray);
            $menuResponse['right_menus'] = $this->setClientMenus($rightMenuArray);
        }

        return new View($menuResponse);
    }

    /**
     * @param array $menus
     *
     * @return array
     */
    private function setClientMenus(
        $menus
    ) {
        $menuArray = array();

        $preSection = 0;

        foreach ($menus as $menu) {
            $position = $menu->getPosition();
            $section = $menu->getSection();
            $part = $menu->getPart();
            $number = $menu->getNumber();

            $sectionStr = strval($section);
            $numberIdx = $number - 1;
            $partIdx = $part - 1;

            $menuData = array(
                'key' => $menu->getKey(),
                'type' => $menu->getType(),
                'name' => '',
                'url' => $menu->getUrl(),
                'ready' => $menu->isReady(),
            );

            if ($section > $preSection) {
                $partArray = array();
                $sectionArray = array();
            }

            $partArray[$numberIdx] = $menuData;
            $sectionArray[$partIdx] = $partArray;
            $menuArray[$sectionStr] = $sectionArray;

            $preSection = $section;
        }

        return $menuArray;
    }
}
