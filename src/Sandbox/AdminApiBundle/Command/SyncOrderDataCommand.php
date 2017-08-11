<?php

namespace Sandbox\AdminApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncOrderDataCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('sandbox:api-bundle:sync:order_data')
            ->setDescription('Sync Order  Data')
            ->addArgument('orderId', InputArgument::REQUIRED, 'order ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arguments = $input->getArguments();
        $orderId = $arguments['orderId'];

        $em = $this->getContainer()->get('doctrine')->getManager();

        $order = $em->getRepository('SandboxApiBundle:Order\ProductOrder')->find($orderId);

        if (!$order->getUnitPrice()) {
            $productInfo = $em->getRepository('SandboxApiBundle:Order\ProductOrderInfo')->findOneBy(array('order' => $orderId));

            if ($productInfo) {
                $info = $productInfo->getProductInfo();

                $info = json_decode($info, true);

                if (isset($info['unit_price']) && !is_null($info['unit_price'])) {
                    $order->setUnitPrice($info['unit_price']);
                    if ($info['base_price']) {
                        $order->setBasePrice($info['base_price']);
                    } else {
                        $seat = $info["room"]["seat"];
                        $order->setBasePrice($seat["base_price"]);
                    }


                } else {
                    if(isset($info['order'])) {
                        $order->setUnitPrice($info['order']['unit_price']);
                        $leasingSets = $info['room']['leasing_set'];

                        var_dump($leasingSets);
                        foreach ($leasingSets as $leasingSet) {
                            if($leasingSet['unit_price'] == $info['order']['unit_price'])
                            {
                                $order->setBasePrice($leasingSet["base_price"]);
                            }
                        }

                    }
                }
            }
        }

        $em->flush();

        $output->writeln('Sync Success!');
    }
}
