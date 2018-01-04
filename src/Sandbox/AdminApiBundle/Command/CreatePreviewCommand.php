<?php

namespace Sandbox\AdminApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreatePreviewCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('sandbox:api-bundle:create:preview')
            ->setDescription('Create Preview Pictures')
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
        $em = $this->getContainer()->get('doctrine')->getManager();

        $imgUrl = $this->getContainer()->getParameter('image_url');

        $entityName = 'Room\RoomAttachment';
        $target = 'building';

        if (isset($input)) {
            $entityName = $input->getOption('entity-name');
        }

        $roomAttachments = $em->getRepository(
                'SandboxApiBundle:'.$entityName
            )
            ->findAll();

        switch ($entityName) {
            case 'Room\RoomAttachment' == $entityName:
                $target = 'building';
                break;
            case 'Event\EventAttachment' == $entityName:
                $target = 'event';
                break;
            default:
                break;
        }

        $dir = '/data/openfire/image/'.$target;

        $previewDir = $dir.'/preview';

        if (!file_exists($previewDir)) {
            mkdir($previewDir, 0777, true);
        }

        foreach ($roomAttachments as $roomAttachment) {
            $file = $roomAttachment->getContent();

            $filename = str_replace($imgUrl.'/'.$target.'/', '', $file);

            $srcImg = $dir.'/'.$filename;

            $previewImg = $previewDir.'/'.$filename;

            if (file_exists($srcImg)) {
                if (!file_exists($previewImg)) {
                    $this->createThumb($srcImg, $previewImg, 100, 100);
                }

                $previewPath = $imgUrl.'/'.$target.'/preview/'.$filename;

                $roomAttachment->setPreview($previewPath);
                $em->persist($roomAttachment);
            }
        }
        $em->flush();

        $output->writeln('Create Preview Pictures Success!');
    }

    private function createThumb(
        $srcImgPath,
        $targetImgPath,
        $dstW,
        $dstH
    ) {
        $src_image = $this->imgCreate($srcImgPath);
        $srcW = imagesx($src_image); //获得图片宽
        $srcH = imagesy($src_image); //获得图片高

        $dst_image = imagecreatetruecolor($dstW, $dstH);

        imagecopyresized($dst_image, $src_image, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

        return imagejpeg($dst_image, $targetImgPath);
    }

    private function imgCreate(
        $srcImgPath
    ) {
        $type = $this->getFileExt($srcImgPath);
        switch ($type) {
            case '.jpg':
                $im = imagecreatefromjpeg($srcImgPath);
                break;
            case '.jpeg':
                $im = imagecreatefromjpeg($srcImgPath);
                break;
            case '.gif':
                $im = imagecreatefromgif($srcImgPath);
                break;
            case '.png':
                $im = imagecreatefrompng($srcImgPath);
                break;
            default:
                $im = null;
        }

        return $im;
    }

    private function getFileExt(
        $oriName
    ) {
        return strtolower(strrchr($oriName, '.'));
    }
}
