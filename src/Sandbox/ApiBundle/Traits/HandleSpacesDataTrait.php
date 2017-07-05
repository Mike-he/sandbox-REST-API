<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\Room\RoomTypeTags;
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
            if (!is_null($space['type_tag'])) {
                $typeTagDescription = $this->get('translator')->trans(RoomTypeTags::TRANS_PREFIX.$space['type_tag']);
                $space['type_tag_description'] = $typeTagDescription;
            }

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
                if ($space['type'] == Room::TYPE_DESK) {
                    $seats = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:Room\RoomFixed')
                        ->findBy(array(
                            'room' => $space['id'],
                        ));

                    $space['product']['seats'] = $seats;
                }

                $space['product']['id'] = $product->getId();
                $space['product']['start_date'] = $product->getStartDate();
                $space['product']['recommend'] = $product->isRecommend();
                $space['product']['visible'] = $product->getVisible();
                $space['product']['sales_recommend'] = $product->isSalesRecommend();

                $favorite = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:User\UserFavorite')
                    ->countFavoritesByObject(
                        UserFavorite::OBJECT_PRODUCT,
                        $product->getId()
                    );

                $space['product']['favorite'] = $favorite;

                $productLeasingSets = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Product\ProductLeasingSet')
                    ->findBy(array('product' => $product));

                foreach ($productLeasingSets as $productLeasingSet) {
                    $space['product']['leasing_sets'][] = array(
                        'base_price' => $productLeasingSet->getBasePrice(),
                        'unit_price' => $productLeasingSet->getUnitPrice(),
                        'amount' => $productLeasingSet->getAmount(),
                    );
                }

                $rentSet = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Product\ProductRentSet')
                    ->findOneBy(array('product' => $product));

                if ($rentSet) {
                    $space['product']['rent_set'] = array(
                        'base_price' => $rentSet->getBasePrice(),
                        'unit_price' => $rentSet->getUnitPrice(),
                        'earliest_rent_date' => $rentSet->getEarliestRentDate(),
                        'status' => $rentSet->isStatus(),
                    );
                }
            }
        }

        return $spaces;
    }
}
