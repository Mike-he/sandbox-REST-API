<?php

namespace Sandbox\ApiBundle\Traits;

/**
 * 云片网短信API
 * 链接：https://www.yunpian.com/api/sms.html.
 *
 * 在PHP 5.5.17 中测试通过。
 * 默认用智能匹配模版接口(send)发送，若需使用模板接口(tpl_send),请自行将代码注释去掉。
 */
trait YunPianSms
{
    use CommonMethod;

    /**
     * 智能匹配模版接口发短信
     * apikey 为云片分配的apikey
     * text 为短信内容
     * mobile 为接受短信的手机号.
     *
     * @param $mobile
     * @param $text
     *
     * @return string
     */
    public function send_sms($mobile, $text)
    {
        $globals = $this->getContainer()
                        ->get('twig')
                        ->getGlobals();

        $url = $globals['sms_api_url'];
        $key = $globals['sms_api_key'];

        $encoded_text = urlencode("$text");
        $mobile = urlencode("$mobile");
        $post_string = "apikey=$key&text=$encoded_text&mobile=$mobile";

        return $this->sock_post($url, $post_string);
    }

    /**
     * @param $phones
     * @param $text
     *
     * @return string
     */
    public function send_marketing_sms(
        $phones,
        $text
    ) {
        $smsV2URL = $this->getParameter('sms_v2_api_url');
        $smsMarktingApiKey = $this->getParameter('sms_marketing_api_key');

        $encoded_text = urlencode("$text");
        $mobile = urlencode("$phones");
        $post_string = "apikey=$smsMarktingApiKey&text=$encoded_text&mobile=$mobile";

        return $this->sock_post($smsV2URL, $post_string);
    }

    /**
     * url 为服务的url地址
     * query 为请求串.
     *
     * @param $url
     * @param $query
     *
     * @return string
     */
    public function sock_post($url, $query)
    {
        $data = '';
        $info = parse_url($url);
        $fp = fsockopen($info['host'], 80, $errno, $errstr, 30);
        if (!$fp) {
            return $data;
        }
        $head = 'POST '.$info['path']." HTTP/1.0\r\n";
        $head .= 'Host: '.$info['host']."\r\n";
        $head .= 'Referer: http://'.$info['host'].$info['path']."\r\n";
        $head .= "Content-type: application/x-www-form-urlencoded\r\n";
        $head .= 'Content-Length: '.strlen(trim($query))."\r\n";
        $head .= "\r\n";
        $head .= trim($query);
        $write = fputs($fp, $head);
        $header = '';
        while ($str = trim(fgets($fp, 4096))) {
            $header .= $str;
        }
        while (!feof($fp)) {
            $data .= fgets($fp, 4096);
        }

        return $data;
    }
}
