<?php

namespace Sandbox\AdminApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreatePreviewCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('sandbox:api-bundle:create:preview')
            ->setDescription('Create Preview Pictures');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getEntityManager();

        $imgUrl = $this->getContainer()->getParameter('image_url');

        $roomAttachments = $em->getRepository('SandboxApiBundle:Room\RoomAttachment')->findAll();

        $dir = '/data/openfire/image/building';

        $previewDir = $dir.'/preview';

        if (!file_exists($previewDir)) {
            mkdir($previewDir, 0777, true);
        }

        foreach ($roomAttachments as $roomAttachment) {
            $filename = $roomAttachment->getFilename();

            $srcImg = $dir.'/'.$filename;

            $previewImg = $previewDir.'/'.$filename;

            if (file_exists($srcImg)) {
                if (!file_exists($previewImg)) {
                    $this->createThumb($srcImg, $previewImg, 100, 100);
                }
            }

            $previewPath = $imgUrl.'/preview/'.$filename;

            $roomAttachment->setPreview($previewPath);
            $em->persist($roomAttachment);
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
}
