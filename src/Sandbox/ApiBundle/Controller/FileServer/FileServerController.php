<?php

namespace Sandbox\ApiBundle\Controller\FileServer;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\View\View;
use OSS\OssClient;
use Sandbox\ApiBundle\Constants\OssConstants;

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
        $domain = $this->container->getParameter('rest_file_server_url');

        return new View(array(
            'file_server_domain' => $domain,
        ));
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="target",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description="target"
     * )
     *
     * @Route("/fileserver/url")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getFileServerUploadUrlAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $domain = $this->container->getParameter('rest_file_server_url');

        return new View(array(
            'file_server_domain' => $domain,
        ));
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="type",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="type"
     * )
     *
     * @Annotations\QueryParam(
     *    name="target",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description="target"
     * )
     *
     * @Annotations\QueryParam(
     *    name="id",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="preview_height",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="preview_height"
     * )
     *
     * @Annotations\QueryParam(
     *    name="preview_width",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="preview_width"
     * )
     *
     * @Annotations\QueryParam(
     *    name="file",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="file"
     * )
     *
     * @Route("/plugins/fileServer/fileservice")
     * @Method({"POST"})
     *
     * @return View
     */
    public function PostFileServerAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $result = $this->upload($request, $paramFetcher);

        return new View($result);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="type",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="type"
     * )
     *
     * @Annotations\QueryParam(
     *    name="target",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description="target"
     * )
     *
     * @Annotations\QueryParam(
     *    name="id",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="preview_height",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="preview_height"
     * )
     *
     * @Annotations\QueryParam(
     *    name="preview_width",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="preview_width"
     * )
     *
     * @Annotations\QueryParam(
     *    name="file",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="file"
     * )
     *
     * @Route("/plugins/fileServer/fileservice/admin")
     * @Method({"POST"})
     *
     * @return View
     */
    public function PostFileServerAdminAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $result = $this->upload($request, $paramFetcher);

        return new View($result);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="type",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="type"
     * )
     *
     * @Annotations\QueryParam(
     *    name="target",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description="target"
     * )
     *
     * @Annotations\QueryParam(
     *    name="id",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="preview_height",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="preview_height"
     * )
     *
     * @Annotations\QueryParam(
     *    name="preview_width",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="preview_width"
     * )
     *
     * @Annotations\QueryParam(
     *    name="file",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="file"
     * )
     *
     * @Route("/plugins/fileServer/fileservice/sales/admin")
     * @Method({"POST"})
     *
     * @return View
     */
    public function PostFileServerSalesAdminAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $result = $this->upload($request, $paramFetcher);

        return new View($result);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="type",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="type"
     * )
     *
     * @Annotations\QueryParam(
     *    name="target",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description="target"
     * )
     *
     * @Annotations\QueryParam(
     *    name="id",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="preview_height",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="preview_height"
     * )
     *
     * @Annotations\QueryParam(
     *    name="preview_width",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="preview_width"
     * )
     *
     * @Annotations\QueryParam(
     *    name="file",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="file"
     * )
     *
     * @Route("/plugins/fileServer/fileservice/shop/admin")
     * @Method({"POST"})
     *
     * @return View
     */
    public function PostFileServerShopAdminAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $result = $this->upload($request, $paramFetcher);

        return new View($result);
    }

    /**
     * @param $request
     * @param $paramFetcher
     *
     * @return array
     */
    private function upload(
        $request,
        $paramFetcher
    ) {
        $type = $paramFetcher->get('type');
        $target = $paramFetcher->get('target');
        $id = $paramFetcher->get('id');
        $preview_height = $paramFetcher->get('preview_height');
        $preview_width = $paramFetcher->get('preview_width');

        //$path = $this->getPath($target, $id);
        $ossClient = $this->getOssClient();
        $path = $this->getOssPath($target, $id);
        $fileid = $this->getName();

        if ($type == 'base64') {
            $file = $request->get('public_b64');
            if (!preg_match('/(?<=\/)[^\/]+(?=\;)/', $file, $pregR)) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }
            $content_type = 'image/'.$pregR[0];
            $filename = $fileid.'.'.$pregR[0];
            $newfile = $path.'/'.$filename;

            preg_match('/(?<=base64,)[\S|\s]+/', $file, $streamForW);
            file_put_contents($newfile, base64_decode($streamForW[0]));
        } else {
            $file = $request->files->get('file');
            if (is_null($file)) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            $content_type = 'image/'.$file->guessExtension();
            $filename = $fileid.'.'.$file->guessExtension();
            //$file->move($path, $filename);

            $object = $path.'/'.$filename;
            $download_link = $this->uploadImage($ossClient, $file, $object);
            $newfile = $path.'/'.$filename;
        }

        if ($type == 'avatar' || $type == 'background') {

              $this->ossThumbImage($ossClient, $object, $path, $type.'_small.jpg', 92, 92);
              $this->ossThumbImage($ossClient, $object, $path, $type.'_medium.jpg', 192, 192);
              $this->ossThumbImage($ossClient, $object, $path, $type.'_large.jpg', 400, 400);

        }

        if ($type == 'image') {
            $this->resizeImage($newfile, $newfile, 800, 800);
        }

        if (!is_null($preview_height) && !is_null($preview_width)) {

            $preview = $path.'/preview';

            $pre_link = $this->ossThumbImage($ossClient, $object, $preview, $filename, $preview_width, $preview_height);
        }

        $preview_link = $preview_height ? $pre_link : $download_link;
        $result = array(
            'content_type' => $content_type,
            'download_link' => $download_link,
            'preview_link' => $preview_link,
            'fileid' => $fileid,
            'filename' => $filename,
            'result' => 0,
        );

        return $result;
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

    private function getOssPath($target, $id)
    {
        $dir = $target;

        if (!is_null($id)) {
            $dir = $target.'/'.$id;
        }

        return $dir;
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

    /**
     * @param $srcImgPath
     * @param $targetImgPath
     * @param $dstW
     * @param $dstH
     */
    private function resizeImage(
        $srcImgPath,
        $targetImgPath,
        $dstW,
        $dstH
    ) {
        $src_image = $this->imgCreate($srcImgPath);
        $srcW = imagesx($src_image); //获得图片宽
        $srcH = imagesy($src_image); //获得图片高

        if (($dstW && $srcW > $dstW) || ($dstH && $srcH > $dstH)) {
            if ($dstW && $srcW > $dstW) {
                $widthratio = $dstW / $srcW;
                $resizewidth_tag = true;
            }

            if ($dstH && $srcH > $dstH) {
                $heightratio = $dstH / $srcH;
                $resizeheight_tag = true;
            }

            if ($resizewidth_tag && $resizeheight_tag) {
                if ($widthratio < $heightratio) {
                    $ratio = $widthratio;
                } else {
                    $ratio = $heightratio;
                }
            }

            if ($resizewidth_tag && !$resizeheight_tag) {
                $ratio = $widthratio;
            }

            if ($resizeheight_tag && !$resizewidth_tag) {
                $ratio = $heightratio;
            }

            $newwidth = $dstW * $ratio;

            $newheight = $srcH * $ratio;

            if (function_exists('imagecopyresampled')) {
                $newim = imagecreatetruecolor($newwidth, $newheight);

                imagecopyresampled($newim, $src_image, 0, 0, 0, 0, $newwidth, $newheight, $srcW, $srcH);
            } else {
                $newim = imagecreate($newwidth, $newheight);

                imagecopyresized($newim, $src_image, 0, 0, 0, 0, $newwidth, $newheight, $srcW, $srcH);
            }

            imagejpeg($newim, $targetImgPath);

            imagedestroy($newim);
        } else {
            imagejpeg($src_image, $targetImgPath);
        }
    }

    private function getOssClient()
    {
        $ak = OssConstants::ACCESS_KEY_ID;
        $sk = OssConstants::ACCESS_KEY_SECRET;
        $endpoint = OssConstants::ENDPOINT;

        return new OssClient($ak,$sk,$endpoint);
    }

    /**
     * @param  $file
     * @param  $object
     * @return string
     */
    private function uploadImage(
        $ossClient,
        $file,
        $object
    ) {
        $endpoint = OssConstants::ENDPOINT;
        $bucket = OssConstants::DEV_BUCKET;
        $ossClient->uploadFile($bucket,  $object, $file);

        $download_link = 'http://'.$bucket.'.'.$endpoint.'/'.$object;

        return $download_link;
    }

    /**
     * @param $object
     * @param $path
     * @param $newfile
     * @param $h
     * @param $w
     * @return string
     */
    private function ossThumbImage($ossClient, $object, $path, $newfile, $h, $w)
    {
        $endpoint = OssConstants::ENDPOINT;
        $bucket = OssConstants::DEV_BUCKET;
        $hight = "h_".$h;
        $width = "w_".$w;
        $options = array(
            OssClient::OSS_FILE_DOWNLOAD => $newfile,
            OssClient::OSS_PROCESS => "image/resize,m_fixed,$hight,$width");

        $ossClient->getObject($bucket, $object, $options);

        $thumb = '/home/vagrant/sandbox-rest-api/web/'.$newfile;
        $ossClient->uploadFile($bucket,  $path.'/'.$newfile, $thumb);
        unlink($thumb);

        $link = 'http://'.$bucket.'.'.$endpoint.'/'.$path.'/'.$newfile;
        return $link;
    }
}
