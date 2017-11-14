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
use Sts\Request\V20150401 as Sts;

/**
 * Class FileServerController.
 */
class FileServerController extends SandboxRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/plugins/fileServer/fileservice/sts")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getStsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        require_once __DIR__.'/sts-server/aliyun-php-sdk-core/Config.php';

        $accessKeyID = $this->container->getParameter('oss_access_key_id');
        $accessKeySecret = $this->container->getParameter('oss_access_key_secret');
        $roleArn = $this->container->getParameter('oss_role_arn');
        $tokenExpire = 900;
        $policy = $this->readFile(__DIR__.'/sts-server/policy/all_policy.txt');

        $iClientProfile = \DefaultProfile::getProfile('cn-hangzhou', $accessKeyID, $accessKeySecret);
        $client = new \DefaultAcsClient($iClientProfile);

        $request = new Sts\AssumeRoleRequest();
        $request->setRoleSessionName('client_name');
        $request->setRoleArn($roleArn);
        $request->setPolicy($policy);
        $request->setDurationSeconds($tokenExpire);
        $response = $client->doAction($request);

        $rows = array();
        $body = $response->getBody();
        $content = json_decode($body);
        $rows['status'] = $response->getStatus();
        if (200 == $response->getStatus()) {
            $rows['AccessKeyId'] = $content->Credentials->AccessKeyId;
            $rows['AccessKeySecret'] = $content->Credentials->AccessKeySecret;
            $rows['Expiration'] = $content->Credentials->Expiration;
            $rows['SecurityToken'] = $content->Credentials->SecurityToken;
        } else {
            $rows['AccessKeyId'] = '';
            $rows['AccessKeySecret'] = '';
            $rows['Expiration'] = '';
            $rows['SecurityToken'] = '';
        }

        return new View($rows);
    }

    private function readFile($fname)
    {
        $content = '';
        if (!file_exists($fname)) {
            echo "The file $fname does not exist\n";
            exit(0);
        }
        $handle = fopen($fname, 'rb');
        while (!feof($handle)) {
            $content .= fread($handle, 10000);
        }
        fclose($handle);

        return $content;
    }

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

        if ('base64' == $type) {
            $file = $request->get('public_b64');
            if (!preg_match('/(?<=\/)[^\/]+(?=\;)/', $file, $pregR)) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }
            $content_type = 'image/'.$pregR[0];
            $filename = $fileid.'.'.$pregR[0];
            $newfile = $_SERVER['DOCUMENT_ROOT'].'/'.$filename;
            $object = $path.'/'.$filename;
            preg_match('/(?<=base64,)[\S|\s]+/', $file, $streamForW);
            file_put_contents($newfile, base64_decode($streamForW[0]));

            $download_link = $this->uploadImage($ossClient, $newfile, $object, $path, $type);
            unlink($newfile);
        } else {
            $file = $request->files->get('file');
            if (is_null($file)) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            $content_type = 'image/'.$file->guessExtension();
            $filename = $fileid.'.'.$file->guessExtension();
            //$file->move($path, $filename);

            $object = $path.'/'.$filename;
            $download_link = $this->uploadImage($ossClient, $file, $object, $path, $type);
        }

        if ('avatar' == $type || 'background' == $type) {
            $object = $path.'/'.$type.'.'.$file->guessExtension();
            $this->ossThumbImage($ossClient, $object, $path, $type.'_small.jpg', 92, 92);
            $this->ossThumbImage($ossClient, $object, $path, $type.'_medium.jpg', 192, 192);
            $this->ossThumbImage($ossClient, $object, $path, $type.'_large.jpg', 400, 400);
        }

        if ('image' == $type) {
            //$this->resizeImage($newfile, $newfile, 800, 800);
            $this->ossResizeImage($ossClient, $object, $filename, 800, 800);
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
        $ak = $this->getParameter('oss_access_key_id');
        $sk = $this->getParameter('oss_access_key_secret');
        $endpoint = $this->getParameter('oss_endpoint');

        return new OssClient($ak, $sk, $endpoint);
    }

    /**
     * @param $ossClient
     * @param $file
     * @param $object
     * @param $path
     * @param $type
     *
     * @return string
     */
    private function uploadImage(
        $ossClient,
        $file,
        $object,
        $path,
        $type
    ) {
        $img_url = $this->getParameter('image_url');
        $bucket = $this->getParameter('oss_bucket');

        if ('avatar' == $type || 'background' == $type) {
            $object = $path.'/'.$type.'.'.$file->guessExtension();
        }
        $ossClient->uploadFile($bucket, $object, $file);

        $download_link = $img_url.'/'.$object;

        return $download_link;
    }

    /**
     * @param $object
     * @param $path
     * @param $newfile
     * @param $h
     * @param $w
     *
     * @return string
     */
    private function ossThumbImage($ossClient, $object, $path, $newfile, $h, $w)
    {
        $img_url = $this->getParameter('image_url');
        $bucket = $this->getParameter('oss_bucket');
        $hight = 'h_'.$h;
        $width = 'w_'.$w;
        $options = array(
            OssClient::OSS_FILE_DOWNLOAD => $newfile,
            OssClient::OSS_PROCESS => "image/resize,m_fixed,$hight,$width", );

        $ossClient->getObject($bucket, $object, $options);

        $thumb = $_SERVER['DOCUMENT_ROOT'].'/'.$newfile;
        $ossClient->uploadFile($bucket, $path.'/'.$newfile, $thumb);
        unlink($thumb);

        $link = $img_url.'/'.$path.'/'.$newfile;

        return $link;
    }

    /**
     * @param $ossClient
     * @param $object
     * @param $newfile
     * @param $h
     * @param $w
     */
    private function ossResizeImage(
        $ossClient,
        $object,
        $newfile,
        $h,
        $w
    ) {
        $bucket = $this->getParameter('oss_bucket');
        $hight = 'h_'.$h;
        $width = 'w_'.$w;
        $options = array(
            OssClient::OSS_FILE_DOWNLOAD => $newfile,
            OssClient::OSS_PROCESS => "image/resize,m_lfit,$hight,$width", );

        $ossClient->getObject($bucket, $object, $options);
        $image = $_SERVER['DOCUMENT_ROOT'].'/'.$newfile;
        $ossClient->uploadFile($bucket, $object, $image);

        unlink($image);
    }
}
