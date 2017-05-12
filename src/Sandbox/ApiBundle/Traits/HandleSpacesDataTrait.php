<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Entity\User\UserFavorite;

/**
 * Handle Data of spaces Trait.
 *
 * @category Sandbox
 *
 * @author   Albert Feng <albert.f@sandbox3.cn>
 * @license  http://www.Sandbox3.cn/ Proprietary
 *
 * @link     http://www.Sandbox3.cn/
 */
trait HandleSpacesDataTrait
{
    private function handleSpacesData(
        $spaces
    ) {
        $limit = 1;
        foreach ($spaces as &$space) {
            $attachment = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomAttachmentBinding')
                ->findAttachmentsByRoom($space['id'], $limit);

            if (!empty($attachment)) {
                $space['content'] = $attachment[0]['content'];
                $space['preview'] = $attachment[0]['preview'];
            }

            $product = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->findOneBy(
                    array(
                        'roomId' => $space['id'],
                        'isDeleted' => false,
                    )
                );

            $space['product'] = [];
            if (!is_null($product)) {
                if ($space['type'] == 'fixed') {
                    $seats = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:Room\RoomFixed')
                        ->findBy(array(
                            'room' => $space['id'],
                        ));

                    $space['product']['seats'] = $seats;
                } else {
                    $space['product']['base_price'] = $product->getBasePrice();
                }

                $space['product']['id'] = $product->getId();
                $space['product']['unit_price'] = $product->getUnitPrice();
                $space['product']['start_date'] = $product->getStartDate();
                $space['product']['recommend'] = $product->isRecommend();
                $space['product']['visible'] = $product->getVisible();
                $space['product']['earliest_rent_date'] = $product->getEarliestRentDate();
                $space['product']['sales_recommend'] = $product->isSalesRecommend();

                $favorite = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:User\UserFavorite')
                    ->countFavoritesByObject(
                        UserFavorite::OBJECT_PRODUCT,
                        $product->getId()
                    );

                $space['product']['favorite'] = $favorite;
            }
        }

        return $spaces;
    }
}
