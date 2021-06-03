<?php


namespace Example\Services;

class RouterService
{
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
        // To ignore the Notice instead of Isset on missing POST vars
        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
        $_SESSION['auth_service'] = $_POST['auth_type'];

        if ($_SESSION['auth_service'] == "jwt") {
            $this->authService = new JWTService();
        } else {
            $this->authService = new CodeGrantService();
        }
    }

    /**
     * The list of controllers for each example
     */
    private const CONTROLLER = [
        'home' => 'Home',
        'home_rooms' => 'Home',
        'home_click' => 'Home',
        'home_monitor' => 'Home',
        'ds_return' => 'DsReturn',
        'must_authenticate' => 'MustAuthenticate',
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
        'eg020' => 'eSignature\EG020SmsAuthentication',
        'eg021' => 'eSignature\EG021PhoneAuthentication',
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
        'eg035' => 'eSignature\EG035SMSDelivery',
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
        'reg007' => 'Rooms\EG007CreateFormGroup',
        'reg008' => 'Rooms\EG008GrantOfficeAccessToFormGroup',
        'reg009' => 'Rooms\Eg009AssignFormToFormGroup',
        'meg001' => 'Monitor\Eg001GetMonitoringData',
    ];

    /**
     * The list of templates with examples
     */
    private const TEMPLATES = [
        "must_authenticate" => "must_authenticate.html",
        "ds_return" => "ds_return.html",
        "home"  => "home.html",
        "home_rooms" => "home_rooms.html",
        "home_monitor" => "home_monitor.html",
        "home_click" => "home_click.html",
        "eg001" => "esignature/eg001_embedded_signing.html",
        "eg002" => "esignature/eg002_signing_via_email.html",
        "eg003" => "esignature/eg003_list_envelopes.html",
        "eg004" => "esignature/eg004_envelope_info.html",
        "eg005" => "esignature/eg005_envelope_recipients.html",
        "eg006" => "esignature/eg006_envelope_docs.html",
        "eg007" => "esignature/eg007_envelope_get_doc.html",
        "eg008" => "esignature/eg008_create_template.html",
        "eg009" => "esignature/eg009_use_template.html",
        "eg010" => "esignature/eg010_send_binary_docs.html",
        "eg011" => "esignature/eg011_embedded_sending.html",
        "eg012" => "esignature/eg012_embedded_console.html",
        "eg013" => "esignature/eg013_add_doc_to_template.html",
        "eg014" => "esignature/eg014_collect_payment.html",
        "eg015" => "esignature/eg015_envelope_tab_data.html",
        "eg016" => "esignature/eg016_set_tab_values.html",
        "eg017" => "esignature/eg017_set_template_tab_values.html",
        "eg018" => "esignature/eg018_envelope_custom_field_data.html",
        "eg019" => "esignature/eg019_access_code_authentication.html",
        "eg020" => "esignature/eg020_sms_authentication.html",
        "eg021" => "esignature/eg021_phone_authentication.html",
        "eg022" => "esignature/eg022_kba_authentication.html",
        "eg023" => "esignature/eg023_idv_authentication.html",
        "eg024" => "esignature/eg024_permissions_creating.html",
        "eg025" => "esignature/eg025_permissions_set_user_group.html",
        "eg026" => "esignature/eg026_permission_change_single_setting.html",
        "eg027" => "esignature/eg027_permissions_delete.html",
        "eg028" => "esignature/eg028_create_brand.html",
        "eg029" => "esignature/eg029_apply_brand_to_envelope.html",
        "eg030" => "esignature/eg030_apply_brand_to_template.html",
        "eg031" => "esignature/eg031_bulk_send.html",
        'eg032' => 'esignature/eg032_pause_signature_workflow.html',
        'eg033' => 'esignature/eg033_unpause_signature_workflow.html',
        'eg034' => 'esignature/eg034_use_conditional_recipients.html',
        "eg035" => "esignature/eg035_sms_delivery.html",
        "reg001" => "rooms/eg001_create_room_with_data.html",
        "reg002" => "rooms/eg002_create_room_with_template.html",
        "reg003" => "rooms/eg003_export_data_from_room.html",
        "reg004" => "rooms/eg004_add_forms_to_room.html",
        "reg005" => "rooms/eg005_get_rooms_with_filters.html",
        "reg006" => "rooms/eg006_create_external_form_fill_session.html",
        'ceg001' => 'click/eg001_create_clickwrap.html',
        'ceg002' => 'click/eg002_activate_clickwrap.html',
        'ceg003' => 'click/eg003_create_clickwrap_version.html',
        'ceg004' => 'click/eg004_get_clickwraps.html',
        'ceg005' => 'click/eg005_get_clickwrap_responses.html',
        "reg007" => "rooms/eg007_create_form_group.html",
        "reg008" => "rooms/eg008_grant_office_access_to_form_group.html",
        "reg009" => "rooms/eg009_assign_form_to_form_group.html",
        "meg001" => "monitor/eg001_get_monitoring_data.html",
    ];

    /**
     * The list of titles for each example
     */
    private const TITLES = [
        "home" => "Home--PHP Code Examples",
        "home_rooms" => "Home--PHP Rooms Code Examples",
        "home_monitor" => "Home--PHP Monitor Code Examples",
        "home_click" => "Home--PHP Click Code Examples",
        "eg001" => "Use embedded signing",
        "eg002" => "Signing via email",
        "eg003" => "List of changed envelopes",
        "eg004" => "Envelope information",
        "eg005" => "Envelope recipient information",
        "eg006" => "Envelope documents",
        "eg007" => "Envelope documents",
        "eg008" => "Create a template",
        "eg009" => "Use a template to send an envelope",
        "eg010" => "Send binary documents",
        "eg011" => "Use embedded sending",
        "eg012" => "Embedded Console",
        "eg013" => "Use Embedded Signing from template and extra doc",
        "eg014" => "Order form with payment",
        "eg015" => "Envelope field data",
        "eg016" => "Set field values",
        "eg017" => "Set template tab values",
        "eg018" => "Envelope custom field data",
        "eg019" => "Access code authentication",
        "eg020" => "SMS Authentication",
        "eg021" => "Phone Authentication",
        "eg022" => "Knowledge Based Authentication",
        "eg023" => "ID Verification Authentication",
        "eg024" => "Permissions creating",
        "eg025" => "Permissions deleting",
        "eg026" => "Permission change single setting",
        "eg027" => "Permissions delete",
        "eg028" => "Create brand",
        "eg029" => "Apply brand to envelope",
        "eg030" => "Apply brand to template",
        "eg031" => "Bulk sending envelopes to multiple recipients",
        'eg032' => 'Pause a signature workflow',
        'eg033' => 'Unpause a signature workflow',
        'eg034' => 'Use conditional recipients',
        "eg035" => "Send an email with SMS delivery",
        "reg001" => "Create room with data",
        "reg002" => "Create room with template",
        "reg003" => "Export data from room",
        "reg004" => "Add forms to room",
        "reg005" => "Get rooms with filters",
        "reg006" => "Create external form fill session",
        "ceg001" => "Create a clickwrap",
        "ceg002" => "Activate a clickwrap",
        "ceg003" => "Create a new clickwrap version",
        "ceg004" => "Get a list of clickwraps",
        "ceg005" => "Get clickwrap responses",
        "reg007" => "Create form group",
        "reg008" => "Grant office access to a form group",
        "reg009" => "Assign a form to a form group",
        "meg001" => "Get monitoring data",
    ];

    /**
     * Page router
     */
    public function router(): void
    {


        $page = $_GET['page'] ?? 'home';


        if ($page == 'home') {

            // We're not logged in and Quickstart is true:  Route to the 1st example.
            if ($GLOBALS['DS_CONFIG']['quickstart'] == 'true' && $this->ds_token_ok() == false  && !isset($_SESSION['beenHere'])) {
                header('Location: ' . $GLOBALS['app_url'] . '/index.php?page=eg001');
            } else {
                error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
                $controller = '\Example\Controllers\Examples\\' . $this->getController($page);
                new $controller($page);
                exit();
            }
        }

        if ($page == 'must_authenticate') {
            if ($GLOBALS['EXAMPLES_API_TYPE']['Monitor'] == true) {
                //Monitor only works via JWT Grant Authentication
                //Let's just shortcut to login immediately
                $this->authService = new JWTService();
                $this->ds_login();
                exit();
            }
            //is it quickstart have they signed in already?
            if ($GLOBALS['DS_CONFIG']['quickstart'] == 'true') {
                //Let's just shortcut to login immediately
                $this->ds_login();
                exit();
            }
            $controller = 'Example\Controllers\Examples\\' . $this->getController($page);
            $c = new $controller();
            $c->controller();
            exit();
        } elseif ($page == 'ds_login') {
            $this->ds_login(); // See below in oauth section
            exit();
        } elseif ($page == 'ds_callback') {
            $this->ds_callback(); // See below in oauth section
            exit();
        } elseif ($page == 'ds_logout') {
            // this variable lets the program know we've already logged in via Quickstart the first time.
            $this->ds_logout(); // See below in oauth section
            exit();
        } elseif ($page == 'ds_return') {
            $GLOBALS['twig']->display('ds_return.html', [
                'title' => 'Returned data',
                'event' => isset($_GET['event']) ? $_GET['event'] : false,
                'envelope_id' => isset($_GET['envelope_id']) ? $_GET['envelope_id'] : false,
                'state' => isset($_GET['state']) ? $_GET['state'] : false
            ]);
            $_SESSION['beenHere'] = true;
            // handle eg001 being listed in project root
        } elseif ($page == 'eg001') {
            // To ignore the Notice instead of Isset on missing POST vars
            error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
            $controller = '\Example\\' .$this->getController($page);
            new $controller($page);
            exit();


        } else {
            // To ignore the Notice instead of Isset on missing POST vars
            error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
            $controller = '\Example\Controllers\Examples\\' . $this->getController($page);
            new $controller($page);
            exit();
        }
    }

    /**
     * @param int $buffer_min buffer time needed in minutes
     * @return boolean $ok true iff the user has an access token that will be good for another buffer min
     */
    function ds_token_ok($buffer_min = 10): bool
    {
        $ok = isset($_SESSION['ds_access_token']) && isset($_SESSION['ds_expiration']);
        $ok = $ok && (($_SESSION['ds_expiration'] - ($buffer_min * 60)) > time());
        return $ok;
    }

    /**
     * Called via a redirect from DocuSign authentication service
     */
    function ds_callback(): void
    {
        # Save the redirect eg if present
        $redirectUrl = false;
        if (isset($_SESSION['eg'])) {
            $redirectUrl = $_SESSION['eg'];
        }
        # JWT login shortcut since user consent was granted
        if (isset($_SESSION['consent_set'])) {
            unset($_SESSION['consent_set']);
            $this->authService = new JWTService();
            $this->ds_login();
        }
        # reset the session
        $this->ds_logout_internal();
        $this->authService->authCallback($redirectUrl);
    }

    /**
     * DocuSign login handler
     */
    function ds_login(): void
    {
        $this->authService->login();
    }

    /**
     * Checker for the CSRF token
     */
    function check_csrf(): void
    {
        $this->authService->checkToken();
    }

    /**
     * Set flash for the current user session
     * @param $msg string
     */
    public function flash(string $msg): void
    {
        $this->authService->flash($msg);
    }

    /**
     * DocuSign logout handler
     */
    function ds_logout(): void
    {
        $this->ds_logout_internal();
        $this->flash('You have logged out from DocuSign.');
        header('Location: ' . $GLOBALS['app_url']);
        exit;
    }

    /**
     * Unset all items from the session
     */
    function ds_logout_internal(): void
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

    }

    /**
     * Get Controller for the template example
     *
     * @param $eg
     * @return mixed
     */
    public function getController($eg)
    {
        return self::CONTROLLER[$eg];
    }

    /**
     * Get the template example
     *
     * @param $eg
     * @return mixed
     */
    public function getTemplate($eg)
    {
        return self::TEMPLATES[$eg];
    }

    /**
     * Get Controller for the template example
     *
     * @param $eg
     * @return mixed
     */
    public function getTitle($eg)
    {
        return self::TITLES[$eg];
    }
}
