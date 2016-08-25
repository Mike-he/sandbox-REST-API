<?php

namespace Sandbox\ApiBundle\Controller\FileServer;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class FileServerController.
 */
class FileServerController extends SandboxRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/fileserver")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getFileServerUrlAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        $domain = $globals['xmpp_domain'];

        return new View(array(
            'file_server_domain' => $domain,
        ));
    }

    /**
     * @param Request $request
     *
     * @Route("/fileserver/upload")
     * @Method({"POST"})
     *
     * @return View
     */
    public function UploadAction(
        Request $request
    ) {
        $type = $request->get('type');
        $target = $request->get('target');
        $id = $request->get('id');
        $preview_height = $request->get('preview_height');
        $preview_width = $request->get('preview_width');

        $path = $this->getPath($target, $id);
        $fileid = $this->getName();
        $content_type = null;

        switch ($type) {
            case 'base64':
                $file = $request->get('file');
                if (!preg_match('/(?<=\/)[^\/]+(?=\;)/', $file, $pregR)) {
                    throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
                };
                $content_type = 'image/'.$pregR[0];
                $filename = $fileid.'.'.$pregR[0];
                $newfile = $path.'/'.$filename;

                preg_match('/(?<=base64,)[\S|\s]+/', $file, $streamForW);
                file_put_contents($newfile, base64_decode($streamForW[0]));
                break;
            default:
                $file = $request->files->get('file');
                if (is_null($file)) {
                    throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
                }
                $dataJson = $this->container->get('serializer')->serialize($file, 'json');
                $dataArray = json_decode($dataJson, true);

                $content_type = $dataArray['mime_type'];
                $filename = $fileid.$this->getFileExt($dataArray['original_name']);

                $newfile = $path.'/'.$filename;
                move_uploaded_file($file->getPathName(), $newfile);
        }

        if ($type == 'avatar' || $type == 'background') {
            copy($newfile, $path.'/'.$type.'.jpg');
            $this->createThumb($newfile, $path.'/'.$type.'_small.jpg', 92, 92);
            $this->createThumb($newfile, $path.'/'.$type.'_medium.jpg', 192, 192);
            $this->createThumb($newfile, $path.'/'.$type.'_large.jpg', 400, 400);
        }

        if (!is_null($preview_height) && !is_null($preview_width)) {
            $preview = $path.'/preview';
            if (!file_exists($preview)) {
                mkdir($preview, 0777, true);
            }
            $this->createThumb($newfile, $preview.'/'.$filename, $preview_width, $preview_height);
        }

        $img_url = $this->container->getParameter('image_url');
        $id = $id ? '/'.$id : '';
        $download_link = $img_url.'/'.$target.$id.'/'.$filename;

        $result = array(
            'content_type' => $content_type,
            'download_link' => $download_link,
            'fileid' => $fileid,
            'filename' => $filename,
            'result' => 0,
        );

        return new View($result);
    }

    /**
     * rename.
     *
     * @return string
     */
    private function getName()
    {
        return md5(uniqid(rand(), true));
    }

    /**
     * @param $oriName
     *
     * @return string
     */
    private function getFileExt(
        $oriName
    ) {
        return strtolower(strrchr($oriName, '.'));
    }

    /**
     * @param $target
     * @param $id
     *
     * @return string
     */
    private function getPath(
        $target,
        $id
    ) {
        $dir = '/data/openfire/image';

        if (!is_null($target)) {
            $dir = $dir.'/'.$target;
        }

        if (!is_null($id)) {
            $dir = $dir.'/'.$id;
        }

        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        return $dir;
    }

    /**
     * Thumbnails.
     *
     * @param $srcImgPath
     * @param $targetImgPath
     * @param $dstW
     * @param $dstH
     *
     * @return bool
     */
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

    /**
     * @param $srcImgPath
     *
     * @return null|resource
     */
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
}
