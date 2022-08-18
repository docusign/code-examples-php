<?php

namespace Example\Controllers;

use Example\Services\RouterService;
use QuickACG\RouterService as QuickRouterService;
use Example\Services\SignatureClientService;
use Example\Services\IRouterService;

abstract class eSignBaseController extends BaseController
{
    private const MINIMUM_BUFFER_MIN = 3;
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
    protected SignatureClientService $clientService;
    protected IRouterService $routerService;
    protected array $args;

    # DCM-3905 The SDK helper method for setting the SigningUIVersion is temporarily unavailable at this time.
    # As a temporary workaround, a raw JSON settings object is passed to sdk methods that use a permission profile.

    # Default settings for updating and creating permissions

    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new SignatureClientService($this->args);
        $this->routerService = $GLOBALS['DS_CONFIG']['quickACG'] === "true" ? new QuickRouterService(): new RouterService();
    }

    abstract function getTemplateArgs(): array;

    /**
     * Base controller
     *
     * @param null $eg
     * @param $brand_languages array|null
     * @param $brands array|null
     * @param $permission_profiles array|null
     * @param $groups array|null
     * @return void
     */
    public function controller(
        $eg = null,
        array $brand_languages = null,
        array $brands = null,
        array $permission_profiles = null,
        array $groups = null
    ): void {
        if (empty($eg)) {
            $eg = static::EG;
        }
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == 'GET') {
            $this->getController(
                $eg,
                basename(static::FILE),
                $brand_languages,
                $brands,
                $permission_profiles,
                $groups
            );
        }
        if ($method == 'POST') {
            $this->routerService->check_csrf();
            $this->createController();
        }
    }

    /**
     * Show the example's form page
     *
     * @param $eg
     * @param $basename string|null
     * @param $brand_languages array|null
     * @param $brands array|null
     * @param $permission_profiles array|null
     * @param $groups array|null
     * @return void
     */
    function getController(
        $eg,
        ?string $basename,
        array $brand_languages = null,
        array $brands = null,
        array $permission_profiles = null,
        array $groups = null
    ): void {
        if ($this->isHomePage($eg)) {
            $GLOBALS['twig']->display(
                $eg . '.html',
                [
                    'title' => $this->homePageTitle($eg),
                    'show_doc' => false
                ]
            );
        } else {
            if ($this->routerService->ds_token_ok()) {
                $pause_envelope_ok = $_SESSION["pause_envelope_id"] ?? false;
                $envelope_id = $_SESSION['envelope_id'] ?? false;
                $template_id = $_SESSION['template_id'] ?? false;
                $envelope_documents = $_SESSION['envelope_documents'] ?? false;
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
                    'title' => $this->routerService->getTitle($eg),
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
                    'source_url' => $GLOBALS['DS_CONFIG']['github_example_url'] . "/eSignature/".  $basename,
                    'documentation' => $GLOBALS['DS_CONFIG']['documentation'] . $eg,
                    'show_doc' => $GLOBALS['DS_CONFIG']['documentation'],
                    'signer_name' => $GLOBALS['DS_CONFIG']['signer_name'],
                    'signer_email' => $GLOBALS['DS_CONFIG']['signer_email'],
                    'pause_envelope_ok' => $pause_envelope_ok
                ];

                $GLOBALS['twig']->display($this->routerService->getTemplate($eg), $displayOptions);
            } else {
                $this->saveCurrentUrlToSession($eg);
                header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=select_api');
                exit;
            }
        }
    }

    /**
     * Declaration for the base controller creator. Each creator should be described in specific Controller
     */
    abstract function createController(): void;

    /**
     * Get static Profile settings
     */
    public function getSettings(): array
    {
        return self::SETTINGS;
    }

    /**
     * @return array
     */
    protected function getDefaultTemplateArgs(): array
    {
        return [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token']
        ];
    }

    /**
     * Check email input value using regular expression
     * @param $email
     * @return string
     */
    protected function checkEmailInputValue($email): string
    {
        return preg_replace('/([^\w +\-\@\.\,])+/', '', $email);
    }

    /**
     * Check input values using regular expressions
     * @param $value
     * @return string
     */
    protected function checkInputValues($value): string
    {
        return preg_replace('/([^\w \-\@\.\,])+/', '', $value);
    }

    /**
     * Check ds
     */
    protected function checkDsToken(): void
    {
        if (!$this->routerService->ds_token_ok(self::MINIMUM_BUFFER_MIN)) {
            $this->clientService->needToReAuth(static::EG);
        }
    }
}
