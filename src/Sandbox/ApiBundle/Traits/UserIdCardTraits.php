<?php

namespace Sandbox\ApiBundle\Traits;

trait UserIdCardTraits
{
    protected function checkIDCardValidation(
        $realName,
        $credentialNo
    ) {
        $validation = $this->validation_filter_id_card($credentialNo);

        if (!$validation) {
            return false;
        }

        $url = $this->getParameter('id_card_auth_api_url');
        $appKey = $this->getParameter('id_card_auth_api_appkey');

        $apiUrl = $url.'?cardNo='.$credentialNo.'&realName='.$realName.'&key='.$appKey;

        $ch = curl_init($apiUrl);
        $result = $this->callAPI($ch, 'GET');

        $resultArray = json_decode($result, true);

        if (!$resultArray['result']['isok']) {
            return false;
        }

        return true;
    }

    /**
     * @param $id_card
     *
     * @return bool
     */
    protected function validation_filter_id_card(
        $id_card
    ) {
        if (18 == strlen($id_card)) {
            return $this->idcard_checksum18($id_card);
        } elseif ((15 == strlen($id_card))) {
            $id_card = $this->idcard_15to18($id_card);

            return $this->idcard_checksum18($id_card);
        } else {
            return false;
        }
    }

    /**
     * @param $idCard_base
     *
     * @return bool|mixed
     */
    protected function idcard_verify_number(
        $idCard_base
    ) {
        if (17 != strlen($idCard_base)) {
            return false;
        }

        // power
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);

        // validation
        $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        $checksum = 0;
        for ($i = 0; $i < strlen($idCard_base); ++$i) {
            $checksum += substr($idCard_base, $i, 1) * $factor[$i];
        }
        $mod = $checksum % 11;
        $verify_number = $verify_number_list[$mod];

        return $verify_number;
    }

    /**
     * @param $idCard
     *
     * @return bool|string
     */
    protected function idcard_15to18(
        $idCard
    ) {
        if (15 != strlen($idCard)) {
            return false;
        } else {
            // after 100 years old person
            if (false !== array_search(substr($idCard, 12, 3), array('996', '997', '998', '999'))) {
                $idCard = substr($idCard, 0, 6).'18'.substr($idCard, 6, 9);
            } else {
                $idCard = substr($idCard, 0, 6).'19'.substr($idCard, 6, 9);
            }
        }
        $idCard = $idCard.$this->idcard_verify_number($idCard);

        return $idCard;
    }

    /**
     * @param $idCard
     *
     * @return bool
     */
    protected function idcard_checksum18(
        $idCard
    ) {
        if (18 != strlen($idCard)) {
            return false;
        }
        $idCard_base = substr($idCard, 0, 17);
        if ($this->idcard_verify_number($idCard_base) != strtoupper(substr($idCard, 17, 1))) {
            return false;
        } else {
            return true;
        }
    }
}
