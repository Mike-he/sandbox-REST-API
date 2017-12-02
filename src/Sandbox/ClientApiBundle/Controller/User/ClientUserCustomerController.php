<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\Order\OrderController;
use Sandbox\ApiBundle\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;

/**
 * Rest controller for User Customer.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
class ClientUserCustomerController extends OrderController
{
    /**
     * @Get("/customers/single")
     *
     * @param Request $request
     */
    public function getCustomerAction(
        Request $request
    ) {
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->find($this->getUserId());
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $customerId = $user->getCustomerId();

        if (is_null($customerId) || empty($customerId)) {
            return new View();
        }

        $customer = $this->retrieveCustomer($customerId);
        $customer = json_decode($customer, true);

        return new View($customer);
    }

    /**
     * @Post("/customers")
     *
     * @param Request $request
     */
    public function createCustomerAction(
        Request $request
    ) {
        $requestContent = json_decode($request->getContent(), true);

        if (array_key_exists('token_id', $requestContent) &&
            array_key_exists('sms_id', $requestContent) &&
            array_key_exists('sms_code', $requestContent)
        ) {
            $token = $requestContent['token_id'];
            $smsId = $requestContent['sms_id'];
            $smsCode = $requestContent['sms_code'];

            $customer = $this->createCustomer($token, $smsId, $smsCode);
            $customer = json_decode($customer, true);

            if (array_key_exists('id', $customer)) {
                $user = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:User\User')
                    ->find($this->getUserId());

                if (!is_null($user)) {
                    $user->setCustomerId($customer['id']);
                }

                $em = $this->getDoctrine()->getManager();
                $em->flush();
            }

            return new View($customer);
        }
    }

    /**
     * @Put("/customers")
     *
     * @param Request $request
     */
    public function putCustomerAction(
        Request $request
    ) {
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->find($this->getUserId());
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $customerId = $user->getCustomerId();

        if (is_null($customerId) || empty($customerId)) {
            $this->throwNotFoundIfNull($customerId, self::NOT_FOUND_MESSAGE);
        }

        $requestContent = json_decode($request->getContent(), true);

        if (array_key_exists('card_id', $requestContent)) {
            $cardId = $requestContent['card_id'];

            $result = $this->putCustomer($customerId, $cardId);
            $result = json_decode($result, true);

            return new View($result);
        }
    }

    /**
     * @Delete("/customers")
     *
     * @param Request $request
     */
    public function removeCustomerAction(
        Request $request
    ) {
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->find($this->getUserId());
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $customerId = $user->getCustomerId();

        if (is_null($customerId) || empty($customerId)) {
            $this->throwNotFoundIfNull($customerId, self::NOT_FOUND_MESSAGE);
        }

        $customer = $this->retrieveCustomer($customerId);
        $customer = json_decode($customer, true);

        if (!is_null($customer) &&
            array_key_exists('deleted', $customer) &&
            $customer['deleted']
        ) {
            $user->setCustomerId(null);

            $em = $this->getDoctrine()->getManager();
            $em->flush();
        }

        return new View($customer);
    }

    /**
     * @Post("/customers/card")
     *
     * @param Request $request
     */
    public function createCustomerCardAction(
        Request $request
    ) {
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->find($this->getUserId());
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $requestContent = json_decode($request->getContent(), true);

        if (array_key_exists('token_id', $requestContent) &&
            array_key_exists('sms_id', $requestContent) &&
            array_key_exists('sms_code', $requestContent)
        ) {
            $token = $requestContent['token_id'];
            $smsId = $requestContent['sms_id'];
            $smsCode = $requestContent['sms_code'];
            $customerId = $user->getCustomerId();

            if (is_null($customerId) || empty($customerId)) {
                $this->throwNotFoundIfNull($customerId, self::NOT_FOUND_MESSAGE);
            }

            $this->createCustomerCard(
                $customerId,
                $token,
                $smsId,
                $smsCode
            );

            $result = $this->retrieveCustomer($customerId);
            $result = json_decode($result, true);

            return new View($result);
        }
    }

    /**
     * @Delete("/customers/card/{cardId}")
     *
     * @param Request $request
     * @param string  $cardId
     */
    public function deleteCustomerCardAction(
        Request $request,
        $cardId
    ) {
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->find($this->getUserId());
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $customerId = $user->getCustomerId();

        if (is_null($customerId) || empty($customerId)) {
            $this->throwNotFoundIfNull($customerId, self::NOT_FOUND_MESSAGE);
        }

        $this->deleteCustomerCard($customerId, $cardId);

        $result = $this->retrieveCustomer($customerId);
        $result = json_decode($result, true);

        return new View($result);
    }

    /**
     * @Get("/customers/cards")
     *
     * @param Request $request
     */
    public function getCustomerCardsAction(
        Request $request
    ) {
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->find($this->getUserId());
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $customerId = $user->getCustomerId();

        if (is_null($customerId) || empty($customerId)) {
            $this->throwNotFoundIfNull($customerId, self::NOT_FOUND_MESSAGE);
        }

        $result = $this->getCustomerCards($customerId);
        $result = json_decode($result, true);

        return new View($result);
    }

    /**
     * @Get("/customers/cards/{cardId}")
     *
     * @param Request $request
     * @param string  $cardId
     */
    public function getSingleCustomerCardAction(
        Request $request,
        $cardId
    ) {
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->find($this->getUserId());
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $customerId = $user->getCustomerId();

        if (is_null($customerId) || empty($customerId)) {
            $this->throwNotFoundIfNull($customerId, self::NOT_FOUND_MESSAGE);
        }

        $result = $this->getSingleCustomerCard($customerId, $cardId);
        $result = json_decode($result, true);

        return new View($result);
    }
}
