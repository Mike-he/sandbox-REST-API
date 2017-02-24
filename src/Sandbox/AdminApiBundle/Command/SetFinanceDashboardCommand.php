<?php

namespace Sandbox\AdminApiBundle\Command;

use Sandbox\ApiBundle\Traits\FinanceTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetFinanceDashboardCommand extends ContainerAwareCommand
{
    use FinanceTrait;

    protected function configure()
    {
        $this->setName('sandbox:api-bundle:set_finance_dashboard')
            ->setDescription('Check Lease Bills');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $now = new \DateTime('now');
        $lastMonthDate = $now->modify('-1 month');
        $year = $lastMonthDate->format('Y');
        $month = $lastMonthDate->format('m');

        $startString = $year.'-'.$month.'-01';
        $startDate = new \DateTime($startString);
        $startDate->setTime(0, 0, 0);

        $endString = $startDate->format('Y-m-t');
        $endDate = new \DateTime($endString);
        $endDate->setTime(23, 59, 59);

        $dashboard = $this->getContainer()
            ->get('doctrine')
            ->getRepository('SandboxApiBundle:Finance\FinanceDashboard')
            ->findOneBy(array(
                'timePeriod' => $year.'-'.$month,
            ));

        if (is_null($dashboard)) {
            $this->generateFinanceDashboardSummary(
                $year,
                $month,
                $startDate,
                $endDate
            );
        }

        $output->writeln('Success!');
    }
}
