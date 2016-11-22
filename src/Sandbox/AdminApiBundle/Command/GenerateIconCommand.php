<?php

namespace Sandbox\AdminApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateIconCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('sandbox:api-bundle:icons:generate')
            ->setDescription('Generate the icons from database')
            ->addOption(
                'entity-name',
                null,
                InputOption::VALUE_REQUIRED,
                'The name of entity',
                null
            )
            ->setHelp(
                <<<EOT
                    The <info>%command.name%</info> command generates the icons.

Usage:
<info>php %command.full_name%</info> --entity-name=Room\\\RoomTypes

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $imageServer = $this->getContainer()->getParameter('image_url');

        $ImagePath = '/data/openfire/image/icon';

        if (!file_exists($ImagePath)) {
            mkdir($ImagePath, 0777, true);
        }

        $types = $entityManager
            ->getRepository(
                'SandboxApiBundle:'.$input->getOption('entity-name')
            )
            ->findBy(array());

        foreach ($types as $type) {
            $icon = $type->getIcon();

            preg_match('/(?<=\/)[^\/]+(?=\;)/', $icon, $imageType);

            if (!isset($imageType[0])) {
                continue;
            }

            $imageName = md5(uniqid(rand(), true)).'.'.$imageType[0];

            preg_match('/(?<=base64,)[\S|\s]+/', $icon, $imageBase64);

            if (!isset($imageBase64[0])) {
                continue;
            }

            file_put_contents($ImagePath.'/'.$imageName, base64_decode($imageBase64[0]));

            $imageUrl = $imageServer.'/icon/'.$imageName;
            $type->setIcon($imageUrl);

            $entityManager->persist($type);
        }

        $entityManager->flush();
    }
}
