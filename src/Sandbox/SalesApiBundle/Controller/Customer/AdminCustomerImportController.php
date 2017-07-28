<?php

namespace Sandbox\SalesApiBundle\Controller\Customer;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Entity\User\UserCustomer;
use Sandbox\ApiBundle\Entity\User\UserCustomerImport;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AdminCustomerImportController extends SalesRestController
{
    const ERROR_FILE_TYPE_WRONG_CODE = 400001;
    const ERROR_FILE_TYPE_WRONG_MESSAGE = 'File type wrong.';

    const ERROR_DATA_REPEAT_CODE = 400002;
    const ERROR_DATA_REPEAT_MESSAGE = 'Exist data repeat error.';

    const ERROR_TEMPLATE_WRONG_CODE = 400003;
    const ERROR_TEMPLATE_WRONG_MESSAGE = 'Template is wrong';

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/customers/import")
     * @Method({"POST"})
     *
     * @return View
     */
    public function importCustomersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $file = $request->files->get('file');
        if (is_null($file)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $extension = $file->guessExtension();
        if ($extension != 'xlsx') {
            return $this->customErrorView(
                400,
                self::ERROR_FILE_TYPE_WRONG_CODE,
                self::ERROR_FILE_TYPE_WRONG_MESSAGE
            );
        }

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $objPHPExcel = \PHPExcel_IOFactory::load($file);

        $sheet = $objPHPExcel->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $em = $this->getDoctrine()->getManager();

        $serialNumber = round(microtime(true) * 1000);

        // check template
        $nameTitle = trim($sheet->getCell('A1')->getValue());
        $phoneCodeTitle = trim($sheet->getCell('B1')->getValue());
        $phoneTitle = trim($sheet->getCell('C1')->getValue());
        $emailTitle = trim($sheet->getCell('D1')->getValue());
        $sexTitle = trim($sheet->getCell('E1')->getValue());
        $nationalityTitle = trim($sheet->getCell('F1')->getValue());
        $idTypeTitle = trim($sheet->getCell('G1')->getValue());
        $idNumberTitle = trim($sheet->getCell('H1')->getValue());
        $languageTitle = trim($sheet->getCell('I1')->getValue());
        $birthdayTitle = trim($sheet->getCell('J1')->getValue());
        $companyNameTitle = trim($sheet->getCell('K1')->getValue());
        $positionTitle = trim($sheet->getCell('L1')->getValue());
        $commentTitle = trim($sheet->getCell('M1')->getValue());

        if (
            $nameTitle != '姓名(必填）'
            || $phoneCodeTitle != '国家代码'
            || $phoneTitle != '手机（必填）'
            || $emailTitle != '邮箱'
            || $sexTitle != '性别'
            || $nationalityTitle != '国籍'
            || $idTypeTitle != '证件类型'
            || $idNumberTitle != '证件号'
            || $languageTitle != '语言'
            || $birthdayTitle != '生日'
            || $companyNameTitle != '公司'
            || $positionTitle != '职位'
            || $commentTitle != '备注'
        ) {
            return $this->customErrorView(
                400,
                self::ERROR_TEMPLATE_WRONG_CODE,
                self::ERROR_TEMPLATE_WRONG_MESSAGE
            );
        }

        for ($j = 2; $j <= $highestRow; ++$j) {
            $name = trim($sheet->getCell('A'.$j)->getValue());
            $phoneCode = trim($sheet->getCell('B'.$j)->getValue());
            $phone = trim($sheet->getCell('C'.$j)->getValue());
            $email = trim($sheet->getCell('D'.$j)->getValue());
            $sex = trim($sheet->getCell('E'.$j)->getValue());
            $nationality = trim($sheet->getCell('F'.$j)->getValue());
            $idType = trim($sheet->getCell('G'.$j)->getValue());
            $idNumber = trim($sheet->getCell('H'.$j)->getValue());
            $language = trim($sheet->getCell('I'.$j)->getValue());
            $birthday = trim($sheet->getCell('J'.$j)->getValue());
            $birthday = gmdate('Y-m-d', \PHPExcel_Shared_Date::ExcelToPHP($birthday));
            $companyName = trim($sheet->getCell('K'.$j)->getValue());
            $position = trim($sheet->getCell('L'.$j)->getValue());
            $comment = trim($sheet->getCell('M'.$j)->getValue());

            $customerImport = new UserCustomerImport();
            $customerImport->setSerialNumber($serialNumber);
            $customerImport->setCompanyId($salesCompanyId);
            $customerImport->setName($name);
            $customerImport->setPhoneCode($phoneCode);
            $customerImport->setPhone($phone);
            $customerImport->setEmail($email);
            $customerImport->setSex($sex);
            $customerImport->setNationality($nationality);
            $customerImport->setIdType($idType);
            $customerImport->setIdNumber($idNumber);
            $customerImport->setLanguage($language);
            $customerImport->setBirthday($birthday);
            $customerImport->setCompanyName($companyName);
            $customerImport->setPosition($position);
            $customerImport->setComment($comment);

            if (is_null($name) || empty($name) || is_null($phoneCode) || empty($phoneCode) || is_null($phone) || empty($phone)) {
                continue;
            }

            $customer = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->findOneBy(array(
                    'phoneCode' => $phoneCode,
                    'phone' => $phone,
                    'companyId' => $salesCompanyId,
                ));
            if ($customer) {
                $customerImport->setStatus(UserCustomerImport::STATUS_REPEAT);
            }

            $em->persist($customerImport);
        }

        $em->flush();

        // check import repeat
        $customerImports = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomerImport')
            ->findBy(array(
                'serialNumber' => $serialNumber,
            ));
        foreach ($customerImports as $import) {
            $customers = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserCustomerImport')
                ->findBy(array(
                    'serialNumber' => $serialNumber,
                    'phoneCode' => $import->getPhoneCode(),
                    'phone' => $import->getPhone(),
                ));

            if (count($customers) > 1) {
                foreach ($customerImports as $item) {
                    $em->remove($item);
                }

                $em->flush();

                return $this->customErrorView(
                    400,
                    self::ERROR_DATA_REPEAT_CODE,
                    self::ERROR_DATA_REPEAT_MESSAGE
                );
            }
        }

        return new View(array(
            'import_serial_number' => $serialNumber,
        ));
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/customers/import_preview/{serialNumber}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getCustomerImportPreviewAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $serialNumber
    ) {
        $customerImports = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomerImport')
            ->findBy(array(
                'serialNumber' => $serialNumber,
            ));

        return new View($customerImports);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/customers/import_confirm")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postCustomerImportConfirmAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['action']) || !isset($data['serial_number'])) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $action = $data['action'];
        $serialNumber = $data['serial_number'];

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        switch ($action) {
            case UserCustomerImport::ACTION_BYPASS:
                $this->bypassCustomerImports(
                    $serialNumber,
                    $salesCompanyId
                );
                break;
            case UserCustomerImport::ACTION_COVER:
                $this->coverCustomerImports(
                    $serialNumber,
                    $salesCompanyId
                );
                break;
        }

        // remove data
        $em = $this->getDoctrine()->getManager();

        $customerImports = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomerImport')
            ->findBy(array(
                'serialNumber' => $serialNumber,
            ));

        foreach ($customerImports as $import) {
            $em->remove($import);
        }

        $em->flush();

        return new View();
    }

    /**
     * @param $serialNumber
     * @param $salesCompanyId
     */
    private function bypassCustomerImports(
        $serialNumber,
        $salesCompanyId
    ) {
        $customerImports = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomerImport')
            ->findBy(array(
                'serialNumber' => $serialNumber,
                'status' => UserCustomerImport::STATUS_NORMAL,
            ));

        if (empty($customerImports)) {
            return;
        }

        $em = $this->getDoctrine()->getManager();

        foreach ($customerImports as $import) {
            $phoneCode = $import->getPhoneCode();
            $phone = $import->getPhone();

            $customer = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->findOneBy(array(
                    'phoneCode' => $phoneCode,
                    'phone' => $phone,
                    'companyId' => $salesCompanyId,
                ));

            if ($customer) {
                continue;
            }

            $customer = new UserCustomer();
            $customer->setPhoneCode($phoneCode);
            $customer->setPhone($phone);
            $customer->setName($import->getName());
            $customer->setCompanyId($import->getCompanyId());
            $customer->setSex($import->getSex());
            $customer->setEmail($import->getEmail());
            $customer->setNationality($import->getNationality());
            $customer->setIdType($import->getIdType());
            $customer->setIdNumber($import->getIdNumber());
            $customer->setLanguage($import->getLanguage());
            $customer->setBirthday($import->getBirthday());
            $customer->setCompanyName($import->getCompanyName());
            $customer->setPosition($import->getPosition());
            $customer->setComment($import->getComment());

            $user = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\User')
                ->findOneBy(array(
                    'phoneCode' => $phoneCode,
                    'phone' => $phone,
                ));

            if ($user) {
                $customer->setUserId($user->getId());
            }

            $em->persist($customer);
        }

        $em->flush();
    }

    /**
     * @param $serialNumber
     * @param $salesCompanyId
     */
    private function coverCustomerImports(
        $serialNumber,
        $salesCompanyId
    ) {
        $this->bypassCustomerImports($serialNumber, $salesCompanyId);

        $customerImports = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomerImport')
            ->findBy(array(
                'serialNumber' => $serialNumber,
                'status' => UserCustomerImport::STATUS_REPEAT,
            ));

        if (empty($customerImports)) {
            return;
        }

        $em = $this->getDoctrine()->getManager();

        foreach ($customerImports as $import) {
            $phoneCode = $import->getPhoneCode();
            $phone = $import->getPhone();

            $customer = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->findOneBy(array(
                    'phoneCode' => $phoneCode,
                    'phone' => $phone,
                    'companyId' => $salesCompanyId,
                ));

            if (!$customer) {
                continue;
            }

            $customer->setPhoneCode($phoneCode);
            $customer->setPhone($phone);
            $customer->setName($import->getName());
            $customer->setCompanyId($import->getCompanyId());
            $customer->setSex($import->getSex());
            $customer->setEmail($import->getEmail());
            $customer->setNationality($import->getNationality());
            $customer->setIdType($import->getIdType());
            $customer->setIdNumber($import->getIdNumber());
            $customer->setLanguage($import->getLanguage());
            $customer->setBirthday($import->getBirthday());
            $customer->setCompanyName($import->getCompanyName());
            $customer->setPosition($import->getPosition());
            $customer->setComment($import->getComment());

            $user = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\User')
                ->findOneBy(array(
                    'phoneCode' => $phoneCode,
                    'phone' => $phone,
                ));

            if ($user) {
                $customer->setUserId($user->getId());
            }
        }

        $em->flush();
    }
}
