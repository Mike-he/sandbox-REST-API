<?php
namespace Sandbox\ApiBundle\Repository\Feed;

use Doctrine\ORM\EntityRepository;

/**
 * Repository for feed
 *
 * @category Sandbox
 * @package  Sandbox\ApiBundle\Controller
 * @author   Josh Yang
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 */
class FeedRepository extends EntityRepository
{

    /**
     * @param $companyID
     */
    public function deleteAllFeedsByCompany(
        $companyID
    ) {
        $query = $this->getEntityManager()
            ->createQuery('
                    DELETE FROM SandboxApiBundle:Feed f
                    WHERE
                    f.parenttype = :parentType
                    AND f.parentid = :parentID
                ')
            ->setParameter('parentType', 'company')
            ->setParameter('parentID', $companyID);

        $query->execute();
    }
}
