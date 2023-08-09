<?php

namespace Example\Controllers\Examples\Admin;

use DocuSign\Admin\Client\ApiException;
use DocuSign\Admin\Model\UserProductPermissionProfilesResponse;
use Example\Controllers\AdminApiBaseController;
use Example\Services\Examples\Admin\DeleteUserProductPermissionProfileByIdService;
use Example\Services\Examples\Admin\RetrieveDocuSignProfileByEmailAddress;
use DocuSign\Admin\Api\ProductPermissionProfilesApi\GetUserProductPermissionProfilesByEmailOptions;

class EG009DeleteUserProductPermissionProfile extends AdminApiBaseController
{
    public const EG = 'aeg009'; # reference (and url) for this example
    public const FILE = __FILE__;
    private const CLM_PRODUCT = 'CLM - ';
    private const ESIGNATURE_PRODUCT = 'eSignature - ';
    private const CLM_PRODUCT_ID = "37f013eb-7012-4588-8028-357b39fdbd00";
    private const ESIGNATURE_PRODUCT_ID = "f6406c68-225c-4e9b-9894-64152a26fa83";
    private const CLM_PROFILES_NOT_FOUND = "No CLM permission profiles are connected to this user";
    private const ESIGN_PROFILES_NOT_FOUND = "No eSignature permission profiles are connected to this user";

    /**
     * Create a new controller instance
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->checkDsToken();

        try {
            if ($_SESSION['email_address'] == null){
                parent::controller(
                    ['email_address' => $_SESSION['email_address']]
                );
            } else {
                $this->orgId = $this->clientService->getOrgAdminId($this->args);

                try {
                    RetrieveDocuSignProfileByEmailAddress::getDocuSignProfileByEmailAddress(
                        $this->orgId,
                        $_SESSION['email_address'],
                        $this->clientService
                    );
                } catch (ApiException $e) {
                    parent::controller(
                        ['email_address' => null]
                    );
                    exit;
                }

                #ds-snippet-start:Admin9Step3
                $permissionProfilesApi = $this->clientService->permProfilesApi();

                $getUserProductPermissionProfilesByEmailOptions = new GetUserProductPermissionProfilesByEmailOptions();
                $getUserProductPermissionProfilesByEmailOptions->setEmail($_SESSION['email_address']);

                $userProductPermissionProfilesResponse = $permissionProfilesApi->getUserProductPermissionProfilesByEmail(
                    $this->orgId,
                    $this->args["account_id"],
                    $getUserProductPermissionProfilesByEmailOptions
                );
                #ds-snippet-end:Admin9Step3

                parent::controller(
                    $this->preparePageProperties($userProductPermissionProfilesResponse)
                );
            }

        } catch (ApiException $e) {
            $this->clientService->showErrorTemplate($e);
        }
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     *
     * @return void
     */
    public function createController(): void
    {
        $this->checkDsToken();

        try {
            $organizationId = $this->clientService->getOrgAdminId();
            $permissionProfilesAPI = $this->clientService->permProfilesApi();

            $removeUserProductsResponse = DeleteUserProductPermissionProfileByIdService::deleteUserProductPermissionProfile(
                $permissionProfilesAPI,
                $organizationId,
                $this->args['account_id'],
                $_SESSION['email_address'],
                $this->args['product_id']
            );

            if ($removeUserProductsResponse) {
                $this->clientService->showDoneTemplateFromManifest(
                    $this->codeExampleText,
                    json_encode($removeUserProductsResponse->__toString())
                );
            }
        } catch (ApiException $e) {
            $this->clientService->showErrorTemplate($e);
        }
    }

    /**
     * Get specific template arguments
     *
     * @return array
     */
    public function getTemplateArgs(): array
    {
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'product_id' =>  $this->checkInputValues($_POST['ProductId']),
        ];
    }

    /**
     * Prepare page properties from the products list.
     *
     * @param UserProductPermissionProfilesResponse $permissionProfiles
     * @return array
     */
    protected function preparePageProperties(UserProductPermissionProfilesResponse $permissionProfiles): array
    {
        $clmPermissionProfiles = $eSignPermissionProfiles = [];

        foreach ($permissionProfiles['product_permission_profiles'] as $productProfile) {
            if ($productProfile['product_name'] == 'CLM') {
                foreach ($productProfile->getPermissionProfiles() as $permissionProfile) {
                    $clmPermissionProfiles[] = $permissionProfile->getPermissionProfileName();
                }
            } else {
                foreach ($productProfile->getPermissionProfiles() as $permissionProfile) {
                    $eSignPermissionProfiles[] = $permissionProfile->getPermissionProfileName();
                }
            }
        }

        $clmPermissionProfilesFormatted = implode(",", $clmPermissionProfiles);
        $eSignPermissionProfilesFormatted = implode(",", $eSignPermissionProfiles);

        $product = [
            self::CLM_PRODUCT_ID => $clmPermissionProfilesFormatted == null
                ? self::CLM_PRODUCT . self::CLM_PROFILES_NOT_FOUND
                : self::CLM_PRODUCT . $clmPermissionProfilesFormatted,
            self::ESIGNATURE_PRODUCT_ID => $eSignPermissionProfilesFormatted == null
                ? self::ESIGNATURE_PRODUCT . self::ESIGN_PROFILES_NOT_FOUND
                : self::ESIGNATURE_PRODUCT . $eSignPermissionProfilesFormatted,
        ];

        return [
            'products' => $product,
            'email_address' => $_SESSION['email_address'],
        ];
    }
}
