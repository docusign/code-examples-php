<?php

namespace Example\Controllers;

use Example\Services\RouterService;

abstract class eSignBaseController extends BaseController
{

    # DCM-3905 The SDK helper method for setting the SigningUIVersion is temporarily unavailable at this time. 
    # As a temporary workaround, a raw JSON settings object is passed to sdk methods that use a permission profile. 

    # Default settings for updating and creating permissions
    private const SETTINGS = [
        "useNewDocuSignExperienceInterface" => "optional",
        "allowBulkSending" => "true",
        "allowEnvelopeSending" => "true",
        "allowSignerAttachments" => "true",
        "allowTaggingInSendAndCorrect" => "true",
        "allowWetSigningOverride" => "true",
        "allowedAddressBookAccess" => "personalAndShared",
        "allowedTemplateAccess" => "share",
        "enableRecipientViewingNotifications" => "true",
        "enableSequentialSigningInterface" => "true",
        "receiveCompletedSelfSignedDocumentsAsEmailLinks" => "false",
        "signingUiVersion" => "v2",
        "useNewSendingInterface" => "true",
        "allowApiAccess" => "true",
        "allowApiAccessToAccount" => "true",
        "allowApiSendingOnBehalfOfOthers" => "true",
        "allowApiSequentialSigning" => "true",
        "enableApiRequestLogging" => "true",
        "allowDocuSignDesktopClient" => "false",
        "allowSendersToSetRecipientEmailLanguage" => "true",
        "allowVaulting" => "false",
        "allowedToBeEnvelopeTransferRecipient" => "true",
        "enableTransactionPointIntegration" => "false",
        "powerFormRole" => "admin",
        "vaultingMode" => "none"
    ];

    /**
     * Base controller
     *
     * @param $eg string
     * @param $routerService RouterService
     * @param $basename string|null
     * @param $brand_languages array|null
     * @param $brands array|null
     * @param $permission_profiles array|null
     * @param $groups array|null
     * @return void
     */
    public function controller(
        string $eg,
        RouterService $routerService,
        $basename = null,
        $brand_languages = null,
        $brands = null,
        $permission_profiles = null,
        $groups = null
    ): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == 'GET') {
            $this->getController($eg, $routerService, $basename, $brand_languages, $brands, $permission_profiles, $groups);
        };
        if ($method == 'POST') {
            $routerService->check_csrf();
            $this->createController();
        };
    }

    /**
     * Show the example's form page
     *
     * @param $eg string
     * @param $routerService RouterService
     * @param $basename string|null
     * @param $brand_languages array|null
     * @param $brands array|null
     * @param $permission_profiles array|null
     * @param $groups array|null
     * @return void
     */
    function getController(
        string $eg,
        RouterService $routerService,
        ?string $basename,
        $brand_languages = null,
        $brands = null,
        $permission_profiles = null,
        $groups = null
    ): void
    {
 
        if ($this->isHomePage($eg)){
                $GLOBALS['twig']->display($eg . '.html', [
                    'title' => $this->homePageTitle($eg),
                    'show_doc' => false
                ]);

        } else {

            if ($routerService->ds_token_ok()) {
                $pause_envelope_ok = isset($_SESSION["pause_envelope_id"]) ? $_SESSION["pause_envelope_id"] : false;
                $envelope_id = isset($_SESSION['envelope_id']) ? $_SESSION['envelope_id'] : false;
                $template_id = isset($_SESSION['template_id']) ? $_SESSION['template_id'] : false;
                $envelope_documents = isset($_SESSION['envelope_documents']) ? $_SESSION['envelope_documents'] : false;
                $gateway = $GLOBALS['DS_CONFIG']['gateway_account_id'];
                $gateway_ok = $gateway && strlen($gateway) > 25;
                $document_options = [];
    
                if ($envelope_documents) {
                    # Prepare the select items
                    $cb = function ($item): array {
                        return ['text' => $item['name'], 'document_id' => $item['document_id']];
                    };
                    $document_options = array_map($cb, $envelope_documents['documents']);
                }
    
                $displayOptions = [
                    'title' => $routerService->getTitle($eg),
                    'template_ok' => $template_id,
                    'envelope_ok' => $envelope_id,
                    'gateway_ok' => $gateway_ok,
                    'documents_ok' => $envelope_documents,
                    'document_options' => $document_options,
                    'languages' => $brand_languages,
                    'brands' => $brands,
                    'groups' => $groups,
                    'permission_profiles' => $permission_profiles,
                    'source_file' => $basename,
                    'source_url' => $GLOBALS['DS_CONFIG']['github_example_url'] . $basename,
                    'documentation' => $GLOBALS['DS_CONFIG']['documentation'] . $eg,
                    'show_doc' => $GLOBALS['DS_CONFIG']['documentation'],
                    'signer_name' => $GLOBALS['DS_CONFIG']['signer_name'],
                    'signer_email' => $GLOBALS['DS_CONFIG']['signer_email'],
                    'pause_envelope_ok' => $pause_envelope_ok
                ];
    
                $GLOBALS['twig']->display($routerService->getTemplate($eg), $displayOptions);
            }

            else {
                

            $this->saveCurrentUrlToSession($eg);
            header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=must_authenticate');
            exit;
        }
    }

    }

    /**
     * Get static Profile settings
     */
    public function getSettings(): array
    {
        return self::SETTINGS;
    }

    /**
     * Declaration for the base controller creator. Each creator should be described in specific Controller
     */
    abstract function createController();
}
