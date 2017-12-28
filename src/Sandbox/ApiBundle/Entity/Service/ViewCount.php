<?php

namespace Sandbox\ApiBundle\Entity\Service;

use Doctrine\ORM\Mapping as ORM;

/**
 * ViewCount.
 *
 * @ORM\Table(name="view_count")
 * @ORM\Entity(repositoryClass="Sandbox\ApiBundle\Repository\Service\ViewCountRepository")
 */
class ViewCount
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="object", type="string", length=64, nullable=false)
     */
    private $object;

    /**
     * @var int
     *
     * @ORM\Column(name="object_id", type="integer", nullable=false)
     */
    private $objectId;

    /**
     * @var int
     *
     * @ORM\Column(name="count", type="integer")
     */
    private $count;
}
