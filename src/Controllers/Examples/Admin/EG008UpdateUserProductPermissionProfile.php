<?php

namespace Example\Controllers\Examples\Admin;

use DocuSign\Admin\Client\ApiException;
use DocuSign\Admin\Model\ProductPermissionProfilesResponse;
use Example\Controllers\AdminApiBaseController;
use Example\Services\Examples\Admin\UpdateUserProductPermissionProfileByEmailService;
use Example\Services\Examples\Admin\RetrieveDocuSignProfileByEmailAddress;
use DocuSign\Admin\ObjectSerializer;

class EG008UpdateUserProductPermissionProfile extends AdminApiBaseController
{
    public const EG = 'aeg008'; # reference (and url) for this example
    public const FILE = __FILE__;
    private const CLM_PRODUCT = 'CLM';
    private const ESIGNATURE_PRODUCT = 'eSignature';

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
            $this->orgId = $this->clientService->getOrgAdminId($this->args);
            
            if ($_SESSION['email_address'] == null){
                parent::controller(
                    ['email_address' => $_SESSION['email_address']]
                );
            } else {
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

                $permissionProfilesAPI = $this->clientService->permProfilesApi();

                $productPermissionProfilesResponse = $permissionProfilesAPI->getProductPermissionProfiles(
                    $this->orgId,
                    $this->args["account_id"]
                );

                parent::controller(
                    $this->preparePageProperties($productPermissionProfilesResponse)
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
            $permissionProfilesAPI = $this->clientService->permProfilesApi();

            $productPermissionProfileResponse = UpdateUserProductPermissionProfileByEmailService::updateUserProductPermissionProfile(
                $permissionProfilesAPI,
                $this->orgId,
                $this->args['account_id'],
                $_SESSION['email_address'],
                $this->args['product_id'],
                $this->args['permission_id']
            );

            if ($productPermissionProfileResponse) {
                $this->clientService->showDoneTemplateFromManifest(
                    $this->codeExampleText,
                    json_encode($productPermissionProfileResponse->__toString())
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
            'product_id' =>  $this->checkInputValues($_POST['Products']),
            'permission_id' => $this->checkInputValues($_POST['PermissionProfilesFiltered']),
        ];
    }

    /**
     * Prepare page properties from the permission profiles list.
     *
     * @param ProductPermissionProfilesResponse $permissionProfiles
     * @return array
     */
    protected function preparePageProperties(ProductPermissionProfilesResponse $permissionProfiles): array
    {
        $eSignProductId = $clmProductId = $clmPermissionProfiles = $eSignPermissionProfiles = "";

        foreach ($permissionProfiles['product_permission_profiles'] as $item) {
            if ($item['product_name'] == self::CLM_PRODUCT) {
                $clmPermissionProfiles = $item->getPermissionProfiles();
                $clmProductId = $item["product_id"];
            } else {
                $eSignPermissionProfiles= $item->getPermissionProfiles();
                $eSignProductId = $item["product_id"];
            }
        }

        $products = [
            $clmProductId => self::CLM_PRODUCT,
            $eSignProductId => self::ESIGNATURE_PRODUCT,
        ];

        $permissionProfiles = json_encode(
            ObjectSerializer::sanitizeForSerialization(
                [
                    self::CLM_PRODUCT => $clmPermissionProfiles,
                    self::ESIGNATURE_PRODUCT => $eSignPermissionProfiles,
                ]
            )
        );

        return [
            'clm_permission_profiles' => $clmPermissionProfiles,
            'permission_profiles' => $permissionProfiles,
            'products' => $products,
            'email_address' => $_SESSION['email_address'],
        ];
    }
}
