<?php

namespace Sandbox\ApiBundle\Traits;

/**
 * Openfire API Trait.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
trait OpenfireApi
{
    use CommonMethod;
    use CurlUtil;

    /**
     * @param string $method
     * @param object $jsonData
     *
     * @return mixed|void
     */
    protected function callOpenfireApnsApi(
        $method,
        $jsonData
    ) {
        try {
            // get globals
            $globals = $this->getContainer()
                            ->get('twig')
                            ->getGlobals();

            // openfire API URL
            $apiURL = $globals['openfire_innet_url'].
                $globals['openfire_plugin_bstios'].
                $globals['openfire_plugin_bstios_apns'];

            return $this->callOpenfireApi($method, $apiURL, $jsonData);
        } catch (\Exception $e) {
            error_log('Call Openfire APNS API went wrong!');
        }
    }

    /**
     * @param string $method
     * @param string $apiURL
     * @param object $jsonData
     *
     * @return mixed|void
     */
    protected function callOpenfireApi(
        $method,
        $apiURL,
        $jsonData
    ) {
        try {
            // init curl
            $ch = curl_init($apiURL);

            // get then response when post OpenFire API
            $response = $this->callAPI($ch, $method, null, $jsonData);

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode != 200) {
                return;
            }

            return $response;
        } catch (\Exception $e) {
            error_log('Call Openfire API went wrong!');
        }
    }

    /**
     * @param $fromJID
     * @param $toJID
     * @param $type
     *
     * @return array
     */
    protected function getHistoryMessage(
        $fromJID,
        $toJID,
        $type,
        $offset = null,
        $limit = null
    ) {
        header('Content-type:text/html;charset=utf-8');  //统一输出编码为utf-8

        $db_host = $this->getContainer()->getParameter('database_host');
        $db_name = $this->getContainer()->getParameter('openfire_db_name');
        $db_user = $this->getContainer()->getParameter('openfire_db_user');
        $db_pwd = $this->getContainer()->getParameter('openfire_db_password');

        $mysqli = mysqli_connect($db_host, $db_user, $db_pwd, $db_name);

        if (!$mysqli) {
            echo mysqli_connect_error();
        }

        mysqli_query($mysqli, 'set names utf8mb4');

        $query = "select * from ofmessagearchive where status IS NOT NULL and toJID = ".$toJID." and type=".$type;

        if (!is_null($fromJID)) {
            $fromJID = '"'.$fromJID.'"';
            $query .= " and fromJID =".$fromJID;
        }

        $query .= " order by sentDate DESC";

        if ($offset && $limit) {
            $query .= " limit ".$offset.','.$limit;
        }

        $result = $mysqli->query($query);
        $message = array();
        if ($result) {
            if ($result->num_rows > 0) {                        //判断结果集中行的数目是否大于0
                while ($row = $result->fetch_array()) {       //循环输出结果集中的记录
                   $message[] = array(
                        'messageID' => $row['messageID'],
                        'conversationID' => $row['conversationID'],
                        'fromJID' => $row['fromJID'],
                        'fromJIDResource' => $row['fromJIDResource'],
                        'toJID' => $row['toJID'],
                        'toJIDResource' => $row['toJIDResource'],
                        'sentDate' => $row['sentDate'],
                        'body' => $row['body'],
                        'type' => $row['type'],
                        'status' => $row['status'],
                        'company' => $row['company'],
                   );
                }
            }
        } else {
            echo '查询失败';
        }
        $mysqli->close();

        return $message;
    }

    /**
     * @param $fromJID
     * @param $toJID
     * @param $type
     *
     * @return array
     */
    protected function getHistoryMessageForService(
        $fromJID,
        $toJID,
        $type,
        $offset = null,
        $limit = null
    ) {
        header('Content-type:text/html;charset=utf-8');  //统一输出编码为utf-8

        $db_host = $this->getContainer()->getParameter('database_host');
        $db_name = $this->getContainer()->getParameter('openfire_db_name');
        $db_user = $this->getContainer()->getParameter('openfire_db_user');
        $db_pwd = $this->getContainer()->getParameter('openfire_db_password');

        $mysqli = mysqli_connect($db_host, $db_user, $db_pwd, $db_name);

        if (!$mysqli) {
            echo mysqli_connect_error();
        }

        mysqli_query($mysqli, 'set names utf8mb4');

        $query = "select * from ofmessagearchive where status IS NOT NULL and type = $type";

        $query .=  "and ((toJID = $toJID and fromJID = $fromJID) or (toJID = $fromJID and fromJID = $toJID ))";

        $query .= " order by sentDate DESC";

        if ($offset && $limit) {
            $query .= " limit ".$offset.','.$limit;
        }

        $result = $mysqli->query($query);
        $message = array();
        if ($result) {
            if ($result->num_rows > 0) {                        //判断结果集中行的数目是否大于0
                while ($row = $result->fetch_array()) {       //循环输出结果集中的记录
                    $message[] = array(
                        'messageID' => $row['messageID'],
                        'conversationID' => $row['conversationID'],
                        'fromJID' => $row['fromJID'],
                        'fromJIDResource' => $row['fromJIDResource'],
                        'toJID' => $row['toJID'],
                        'toJIDResource' => $row['toJIDResource'],
                        'sentDate' => $row['sentDate'],
                        'body' => $row['body'],
                        'type' => $row['type'],
                        'status' => $row['status'],
                        'company' => $row['company'],
                    );
                }
            }
        } else {
            echo '查询失败';
        }
        $mysqli->close();

        return $message;
    }
}
