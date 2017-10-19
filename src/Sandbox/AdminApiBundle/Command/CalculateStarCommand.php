<?php

namespace Sandbox\AdminApiBundle\Command;

use Sandbox\ApiBundle\Entity\Evaluation\Evaluation;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CalculateStarCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('sandbox:api-bundle:calculate:star')
            ->setDescription('Calculate Evaluation Star');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $buildings = $em->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->findBy(
                array(
                    'visible' => true,
                    'isDeleted' => false,
                )
            );

        foreach ($buildings as $building) {
            $officialStar = $em->getRepository('SandboxApiBundle:Evaluation\Evaluation')
                ->findOneBy(
                    array(
                        'buildingId' => $building,
                        'type' => Evaluation::TYPE_OFFICIAL,
                    )
                );

            $buildingStarCount = $em->getRepository('SandboxApiBundle:Evaluation\Evaluation')
                ->countEvaluation(
                    $building,
                    Evaluation::TYPE_BUILDING,
                    true
                );

            $orderStarCount = $em->getRepository('SandboxApiBundle:Evaluation\Evaluation')
                ->countEvaluation(
                    $building,
                    Evaluation::TYPE_ORDER,
                    true
                );

            $buildingStarSum = $em->getRepository('SandboxApiBundle:Evaluation\Evaluation')
                ->sumEvaluation(
                    $building,
                    Evaluation::TYPE_BUILDING,
                    true
                );

            $orderStarSum = $em->getRepository('SandboxApiBundle:Evaluation\Evaluation')
                ->sumEvaluation(
                    $building,
                    Evaluation::TYPE_ORDER,
                    true
                );

            $buildingStar = 0;
            $orderStar = 0;

            if ($buildingStarCount > 0) {
                $buildingStar = $buildingStarSum / $buildingStarCount;
            }

            if ($orderStarCount > 0) {
                $orderStar = $orderStarSum / $orderStarCount;
            }

            if ($officialStar && ($buildingStarCount + $orderStarCount) >= 10) {
                if (0 == $buildingStarCount) {
                    $evaluationStar = ($officialStar->getTotalStar() + $orderStar) * 0.5;
                } elseif (0 == $orderStarCount) {
                    $evaluationStar = ($officialStar->getTotalStar() + $buildingStar) * 0.5;
                } else {
                    $evaluationStar = $officialStar->getTotalStar() * 0.5 + $buildingStar * 0.1 + $orderStar * 0.4;
                }
                $building->setEvaluationStar(round($evaluationStar, 2));
            }

            $building->setBuildingStar(round($buildingStar, 2));
            $building->setOrderStar(round($orderStar, 2));
            $building->setBuildingEvaluationNumber($buildingStarCount);
            $building->setOrderEvaluationNumber($orderStarCount);

            $em->persist($building);
        }
        $em->flush();

        $output->writeln('Calculate Evaluation Star Success!');
    }
}
