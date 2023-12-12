<?php

namespace DocuSign\Services;

use DocuSign\Controllers\BaseController;

class RouterService implements IRouterService
{
    /**
     * The list of controllers for each example
     */
    private const CONTROLLER = [
        'home_esig' => 'Home',
        'ds_return' => 'DsReturn',
        BaseController::LOGIN_REDIRECT => 'MustAuthenticate',
        'eg001' => 'EG001EmbeddedSigning',
        'eg002' => 'eSignature\EG002SigningViaEmail',
        'eg003' => 'eSignature\EG003ListEnvelopes',
        'eg004' => 'eSignature\EG004EnvelopeInfo',
        'eg005' => 'eSignature\EG005EnvelopeRecipients',
        'eg006' => 'eSignature\EG006EnvelopeDocs',
        'eg007' => 'eSignature\EG007EnvelopeGetDoc',
        'eg008' => 'eSignature\EG008CreateTemplate',
        'eg009' => 'eSignature\EG009UseTemplate',
        'eg010' => 'eSignature\EG010SendBinaryDocs',
        'eg011' => 'eSignature\EG011EmbeddedSending',
        'eg012' => 'eSignature\EG012EmbeddedConsole',
        'eg013' => 'eSignature\EG013AddDocToTemplate',
        'eg014' => 'eSignature\EG014CollectPayment',
        'eg015' => 'eSignature\EG015EnvelopeTabData',
        'eg016' => 'eSignature\EG016SetTabValues',
        'eg017' => 'eSignature\EG017SetTemplateTabValues',
        'eg018' => 'eSignature\EG018EnvelopeCustomFieldData',
        'eg019' => 'eSignature\EG019AccessCodeAuthentication',
        'eg020' => 'eSignature\EG020PhoneAuthentication',
        'eg022' => 'eSignature\EG022KbAuthentication',
        'eg023' => 'eSignature\EG023IDVAuthentication',
        'eg024' => 'eSignature\EG024PermissionCreate',
        'eg025' => 'eSignature\EG025PermissionSetUserGroup',
        'eg026' => 'eSignature\EG026PermissionChangeSingleSetting',
        'eg027' => 'eSignature\EG027PermissionDelete',
        'eg028' => 'eSignature\EG028CreateBrand',
        'eg029' => 'eSignature\EG029ApplyBrandToEnvelope',
        'eg030' => 'eSignature\EG030ApplyBrandToTemplate',
        'eg031' => 'eSignature\EG031BulkSendEnvelopes',
        'eg032' => 'eSignature\EG032PauseSignatureWorkflow',
        'eg033' => 'eSignature\EG033UnpauseSignatureWorkflow',
        'eg034' => 'eSignature\EG034UseConditionalRecipients',
        'eg035' => 'eSignature\EG035ScheduledSending',
        'eg036' => 'eSignature\EG036DelayedRouting',
        'eg037' => 'eSignature\EG037SMSDelivery',
        'eg038' => 'eSignature\EG038ResponsiveSigning',
        'eg039' => 'eSignature\EG039InPersonSigning',
        'eg040' => 'eSignature\EG040SetDocumentsVisibility',
        'eg041' => 'eSignature\EG041CFREmbeddedSigning',
        'eg042' => 'eSignature\EG042DocumentGeneration',
        'eg043' => 'eSignature\EG043SharedAccess',
        'eg044' => 'eSignature\EG044FocusedView',
        'eg043/AuthRequest' => 'eSignature\EG043AuthRequest',
        'eg043/EnvelopesListStatus' => 'eSignature\EG043EnvelopesListStatus',
        'reg001' => 'Rooms\EG001CreateRoomWithData',
        'reg002' => 'Rooms\EG002CreateRoomWithTemplate',
        'reg003' => 'Rooms\EG003ExportDataFromRoom',
        'reg004' => 'Rooms\EG004AddFormsToRoom',
        'reg005' => 'Rooms\EG005GetRoomsWithFilters',
        'reg006' => 'Rooms\EG006CreateExternalFormFillSession',
        'ceg001' => 'Click\EG001CreateClickwrap',
        'ceg002' => 'Click\EG002ActivateClickwrap',
        'ceg003' => 'Click\EG003CreateClickwrapVersion',
        'ceg004' => 'Click\EG004GetClickwraps',
        'ceg005' => 'Click\EG005GetClickwrapResponses',
        'ceg006' => 'Click\EG006EmbedClickwrap',
        'reg007' => 'Rooms\EG007CreateFormGroup',
        'reg008' => 'Rooms\EG008GrantOfficeAccessToFormGroup',
        'reg009' => 'Rooms\Eg009AssignFormToFormGroup',
        'meg001' => 'Monitor\Eg001GetMonitoringData',
        'aeg001' => 'Admin\EG001CreateNewUser',
        'aeg002' => 'Admin\EG002CreateActiveCLMESignUser',
        'aeg003' => 'Admin\EG003BulkExportUserData',
        'aeg003a' => 'Admin\EG003aCheckRequestStatus',
        'aeg004' => 'Admin\EG004BulkImportUserData',
        'aeg004a' => 'Admin\EG004aCheckImportRequestStatus',
        'aeg005' => 'Admin\EG005AuditUsers',
        'aeg006' => 'Admin\EG006RetrieveDocuSignProfileByEmailAddress',
        'aeg007' => 'Admin\EG007RetrieveDocuSignProfileByUserID',
        'aeg008' => 'Admin\EG008UpdateUserProductPermissionProfile',
        'aeg009' => 'Admin\EG009DeleteUserProductPermissionProfile',
        'aeg010' => 'Admin\EG010DeleteUserDataFromOrganization',
        'aeg011' => 'Admin\EG011DeleteUserDataFromAccount',
        'aeg012' => 'Admin\EG012CloneAccount',
        'con001' => 'Connect\Eg001ValidateUsingHmac',
    ];
    /**
     * The list of templates with examples
     */
    private const TEMPLATES = [
        BaseController::LOGIN_REDIRECT => 'must_authenticate.html',
        'ds_return' => 'ds_return.html',
        'home_esig' => 'home_esig.html',
        'eg001' => 'esignature/eg001_embedded_signing.html',
        'eg002' => 'esignature/eg002_signing_via_email.html',
        'eg003' => 'esignature/eg003_list_envelopes.html',
        'eg004' => 'esignature/eg004_envelope_info.html',
        'eg005' => 'esignature/eg005_envelope_recipients.html',
        'eg006' => 'esignature/eg006_envelope_docs.html',
        'eg007' => 'esignature/eg007_envelope_get_doc.html',
        'eg008' => 'esignature/eg008_create_template.html',
        'eg009' => 'esignature/eg009_use_template.html',
        'eg010' => 'esignature/eg010_send_binary_docs.html',
        'eg011' => 'esignature/eg011_embedded_sending.html',
        'eg012' => 'esignature/eg012_embedded_console.html',
        'eg013' => 'esignature/eg013_add_doc_to_template.html',
        'eg014' => 'esignature/eg014_collect_payment.html',
        'eg015' => 'esignature/eg015_envelope_tab_data.html',
        'eg016' => 'esignature/eg016_set_tab_values.html',
        'eg017' => 'esignature/eg017_set_template_tab_values.html',
        'eg018' => 'esignature/eg018_envelope_custom_field_data.html',
        'eg019' => 'esignature/eg019_access_code_authentication.html',
        'eg020' => 'esignature/eg020_phone_authentication.html',
        'eg022' => 'esignature/eg022_kba_authentication.html',
        'eg023' => 'esignature/eg023_idv_authentication.html',
        'eg024' => 'esignature/eg024_permissions_creating.html',
        'eg025' => 'esignature/eg025_permissions_set_user_group.html',
        'eg026' => 'esignature/eg026_permission_change_single_setting.html',
        'eg027' => 'esignature/eg027_permissions_delete.html',
        'eg028' => 'esignature/eg028_create_brand.html',
        'eg029' => 'esignature/eg029_apply_brand_to_envelope.html',
        'eg030' => 'esignature/eg030_apply_brand_to_template.html',
        'eg031' => 'esignature/eg031_bulk_send.html',
        'eg032' => 'esignature/eg032_pause_signature_workflow.html',
        'eg033' => 'esignature/eg033_unpause_signature_workflow.html',
        'eg034' => 'esignature/eg034_use_conditional_recipients.html',
        'eg035' => 'esignature/eg035_scheduled_sending.html',
        'eg036' => 'esignature/eg036_delayed_routing.html',
        'eg037' => 'esignature/eg037_sms_delivery.html',
        'eg038' => 'esignature/eg038_responsive_signing.html',
        'eg039' => 'esignature/eg039_in_person_signing.html',
        'eg040' => 'esignature/eg040_set_document_visibility.html',
        'eg041' => 'esignature/eg041_cfr_embedded_signing.html',
        'eg042' => 'esignature/eg042_document_generation.html',
        'eg043' => 'esignature/eg043_shared_access.html',
        'eg044' => 'esignature/eg044_focused_view.html',
        'reg001' => 'rooms/eg001_create_room_with_data.html',
        'reg002' => 'rooms/eg002_create_room_with_template.html',
        'reg003' => 'rooms/eg003_export_data_from_room.html',
        'reg004' => 'rooms/eg004_add_forms_to_room.html',
        'reg005' => 'rooms/eg005_get_rooms_with_filters.html',
        'reg006' => 'rooms/eg006_create_external_form_fill_session.html',
        'ceg001' => 'click/eg001_create_clickwrap.html',
        'ceg002' => 'click/eg002_activate_clickwrap.html',
        'ceg003' => 'click/eg003_create_clickwrap_version.html',
        'ceg004' => 'click/eg004_get_clickwraps.html',
        'ceg005' => 'click/eg005_get_clickwrap_responses.html',
        'ceg006' => 'click/eg006_embed_clickwrap.html',
        'reg007' => 'rooms/eg007_create_form_group.html',
        'reg008' => 'rooms/eg008_grant_office_access_to_form_group.html',
        'reg009' => 'rooms/eg009_assign_form_to_form_group.html',
        'meg001' => 'monitor/eg001_get_monitoring_data.html',
        'aeg001' => 'admin/eg001_create_active_user.html',
        'aeg002' => 'admin/eg002_create_new_esignature_clm_user.html',
        'aeg003' => 'admin/eg003_bulk_export_user_data.html',
        'aeg003a' => 'admin/eg003a_check_request_status.html',
        'aeg004' => 'admin/eg004_bulk_import_user_data.html',
        'aeg004a' => 'admin/eg004a_check_import_request_status.html',
        'aeg005' => 'admin/eg005_audit_users.html',
        'aeg006' => 'admin/eg006_retrieve_profile_by_email_address.html',
        'aeg007' => 'admin/eg007_retrieve_profile_by_user_id.html',
        'aeg008' => 'admin/eg008_update_user_product_permission_profile.html',
        'aeg009' => 'admin/eg009_delete_user_product_permission_profile.html',
        'aeg010' => 'admin/eg010_delete_user_data_from_organization.html',
        'aeg011' => 'admin/eg011_delete_user_data_from_account.html',
        'con001' => 'connect/eg001_validate_using_hmac.html',
    ];
    
    /**
     * The list of controllers for each example
     */
    public $authService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        if (!isset($_SESSION['api_type'])) {
            // first time loading server and session is null causes problems for manifestService
            $_SESSION['api_type'] = 'eSign';
        }
        if (!isset($_SESSION['API_TEXT']) && isset($GLOBALS['DS_CONFIG'])) {
            // load manifest data
            $_SESSION['API_TEXT'] = ManifestService::loadManifestData($GLOBALS['DS_CONFIG']['CodeExamplesManifest']);
        }
        
        // To ignore the Notice instead of Isset on missing POST vars
        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

        if (isset($_POST['auth_type'])) {
            $_SESSION['auth_service'] = $_POST['auth_type'];
        } elseif ($GLOBALS['DS_CONFIG']['quickstart'] == 'true' && !isset($_SESSION['beenHere'])) {
            $_SESSION['auth_service'] = 'code_grant';
        }

        if (isset($_POST['api_type'])) {
            $_SESSION['api_type'] = $_POST['api_type'];
        }

        if ($_SESSION['auth_service'] == 'code_grant') {
            $this->authService = new CodeGrantService();
        } else {
            $this->authService = new JWTService();
        }
    }


    /**
     * Page router
     */
    public function router(): void
    {
        $homeRoute = 'home_esig';
        
        $page = $_GET['page'] ?? $homeRoute;
        if ($page == $homeRoute) {
            // We're not logged in and Quickstart is true:  Route to the 1st example.
            if ($GLOBALS['DS_CONFIG']['quickstart'] == 'true' && $_SESSION['beenHere'] == 'true') {
                $_SESSION['beenHere'] = 'false';
                $_SESSION['api_type'] = ApiTypes::ESIGNATURE;

                if ($_SESSION['cfr_enabled'] == 'enabled') {
                    header('Location: ' . $GLOBALS['app_url'] . '/index.php?page=eg041');
                } else {
                    header('Location: ' . $GLOBALS['app_url'] . '/index.php?page=eg001');
                }
            } elseif (isset($_SESSION['userflow_example_43'])) {
                unset($_SESSION['userflow_example_43']);
                $page = 'eg043/EnvelopesListStatus';
                $_GET['page'] = $page;
                
                // To ignore the Notice instead of Isset on missing POST vars
                error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
                $controller = '\DocuSign\Controllers\Examples\\' . $this->getController($page);
                new $controller($page);
                exit();
            } else {
                error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
                $controller = '\DocuSign\Controllers\Examples\\' . $this->getController($page);
                new $controller($page);
                exit();
            }
        }

        if ($page == BaseController::LOGIN_REDIRECT) {
            if ($_SESSION['prefered_api_type'] === null) {
                $_SESSION['prefered_api_type'] = ApiTypes::ESIGNATURE;
            }

            if ($_SESSION['prefered_api_type'] == 'Monitor' || $_SESSION['prefered_api_type'] == 'Connect') {
                //Monitor only works via JWT Grant Authentication
                //Let's just shortcut to login immediately
                $this->authService = new JWTService();
                $this->dsLogin();
                exit();
            }
            //is it quickstart have they signed in already?
            if ($GLOBALS['DS_CONFIG']['quickstart'] == 'true' && !isset($_SESSION['beenHere'])) {
                //Let's just shortcut to login immediately
                // we should default to ESignature for the first runthrough
                $_SESSION['beenHere'] = 'true';
                $this->authService = new CodeGrantService();
                $this->dsLogin();
                exit();
            }
            $controller = 'DocuSign\Controllers\Examples\\' . $this->getController($page);
            $c = new $controller();
            $c->controller();
            exit();
        } elseif ($page == 'ds_login') {
            $this->dsLogin(); // See below in oauth section
            exit();
        } elseif ($page == 'ds_callback') {
            $this->dsCallback(); // See below in oauth section
            exit();
        } elseif ($page == 'ds_logout') {
            $this->dsLogout(); // See below in oauth section
            exit();
        } elseif ($page == 'ds_return') {
            $GLOBALS['twig']->display(
                'ds_return.html',
                [
                    'title' => 'Returned data',
                    'event' => $_GET['event'] ?? false,
                    'envelope_id' => $_GET['envelope_id'] ?? false,
                    'state' => $_GET['state'] ?? false,
                    'common_texts' => ManifestService::getCommonTexts()
                ]
            );
            // this variable lets the program know we've already logged in via Quickstart the first time.
            // handle eg001 being listed in project root
        } elseif ($page == 'eg001') {
            // To ignore the Notice instead of Isset on missing POST vars
            error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
            $controller = '\DocuSign\\' . $this->getController($page);
            new $controller($page);
            exit();
        } else {
            // To ignore the Notice instead of Isset on missing POST vars
            error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
            $controller = '\DocuSign\Controllers\Examples\\' . $this->getController($page);
            new $controller($page);
            exit();
        }
    }

    /**
     * @param int $buffer_min buffer time needed in minutes
     * @return boolean $ok true iff the user has an access token that will be good for another buffer min
     */
    public function dsTokenOk(int $buffer_min = 10): bool
    {
        $ok = isset($_SESSION['ds_access_token']) && isset($_SESSION['ds_expiration']);
        return $ok && (($_SESSION['ds_expiration'] - ($buffer_min * 60)) > time());
    }

    
    /**
     * Get Controller for the template example
     *
     * @param $eg
     * @return string
     */
    public function getController($eg): string
    {
        return self::CONTROLLER[$eg];
    }

    /**
     * Unset all items from the session
     */
    public function dsLogoutInternal(): void
    {
        if (isset($_SESSION['ds_access_token'])) {
            unset($_SESSION['ds_access_token']);
        }
        if (isset($_SESSION['ds_refresh_token'])) {
            unset($_SESSION['ds_refresh_token']);
        }
        if (isset($_SESSION['ds_user_email'])) {
            unset($_SESSION['ds_user_email']);
        }
        if (isset($_SESSION['ds_user_name'])) {
            unset($_SESSION['ds_user_name']);
        }
        if (isset($_SESSION['prefered_api_type'])) {
            unset($_SESSION['prefered_api_type']);
        }
        if (isset($_SESSION['ds_expiration'])) {
            unset($_SESSION['ds_expiration']);
        }
        if (isset($_SESSION['ds_account_id'])) {
            unset($_SESSION['ds_account_id']);
        }
        if (isset($_SESSION['ds_account_name'])) {
            unset($_SESSION['ds_account_name']);
        }
        if (isset($_SESSION['ds_base_path'])) {
            unset($_SESSION['ds_base_path']);
        }
        if (isset($_SESSION['envelope_id'])) {
            unset($_SESSION['envelope_id']);
        }
        if (isset($_SESSION['eg'])) {
            unset($_SESSION['eg']);
        }
        if (isset($_SESSION['envelope_documents'])) {
            unset($_SESSION['envelope_documents']);
        }
        if (isset($_SESSION['template_id'])) {
            unset($_SESSION['template_id']);
        }
        if (isset($_SESSION['api_type'])) {
            unset($_SESSION['api_type']);
        }
        if (isset($_SESSION['auth_service'])) {
            unset($_SESSION['auth_service']);
        }
    }

    /**
     * DocuSign login handler
     */
    public function dsLogin(): void
    {
        $_SESSION['api_type'] = $_SESSION['prefered_api_type'];
        $this->authService->login();
    }

    /**
     * Called via a redirect from DocuSign authentication service
     */
    public function dsCallback(): void
    {
        // Save the redirect eg if present
        $redirectUrl = false;
        if (isset($_SESSION['eg'])) {
            $redirectUrl = $_SESSION['eg'];
        }
        // reset the session
        $tempAPIType = $_SESSION['api_type'];
        $tempGrant = $_SESSION['auth_service'];
        $this->dsLogoutInternal();

        // Workaround for ACG apiTypePicker
        $_SESSION['api_type'] = $tempAPIType;
        $_SESSION['auth_service'] = $tempGrant;
        $this->authService->authCallback($redirectUrl);
    }

    /**
     * DocuSign logout handler
     */
    public function dsLogout(): void
    {
        $this->dsLogoutInternal();
        $this->flash('You have logged out from DocuSign.');
        header('Location: ' . $GLOBALS['app_url']);
        exit;
    }

    /**
     * Set flash for the current user session
     *
     * @param $msg string
     */
    public function flash(string $msg): void
    {
        $this->authService->flash($msg);
    }

    /**
     * Checker for the CSRF token
     */
    public function checkCsrf(): void
    {
        $this->authService->checkToken();
    }

    /**
     * Get the template example
     *
     * @param $eg
     * @return string
     */
    public function getTemplate($eg): string
    {
        return self::TEMPLATES[$eg];
    }

    /**
     * Get Controller for the template example
     *
     * @param $eg
     * @return string
     */
    public function getTitle($eg): string
    {
        return ManifestService::getPageText($eg)['ExampleName'];
    }
}
