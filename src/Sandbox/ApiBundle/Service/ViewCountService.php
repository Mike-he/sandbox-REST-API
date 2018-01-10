<?php

namespace Sandbox\ApiBundle\Service;

use Sandbox\ApiBundle\Entity\Service\ViewCounts;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ViewCountService
{
    private $container;
    private $doctrine;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->doctrine = $this->container->get('doctrine');
    }

    public function autoCounting(
        $object,
        $objectId,
        $type
    ) {
        $em = $this->doctrine->getManager();

        $viewCount = $em->getRepository('SandboxApiBundle:Service\ViewCounts')
            ->findOneBy(array(
                'object' => $object,
                'objectId' => $objectId,
                'type' => $type,
            ));

        if (is_null($viewCount)) {
            $viewCount = new ViewCounts();
            $viewCount->setObject($object);
            $viewCount->setObjectId($objectId);
            $viewCount->setType($type);
            $viewCount->setCount(1);
            $em->persist($viewCount);
        } else {
            $count = $viewCount->getCount() + 1;
            $viewCount->setCount($count);
        }

        $em->persist($viewCount);
        $em->flush();
    }
}
