<?php


namespace Example\Services;

use Example\Services\CodeGrantService;

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
        error_reporting(E_ALL & ~E_NOTICE);
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
        'ds_return' => 'DsReturn',
        'must_authenticate' => 'MustAuthenticate',
        'eg001' => 'EG001EmbeddedSigning',
        'eg002' => 'EG002SigningViaEmail',
        'eg003' => 'EG003ListEnvelopes',
        'eg004' => 'EG004EnvelopeInfo',
        'eg005' => 'EG005EnvelopeRecipients',
        'eg006' => 'EG006EnvelopeDocs',
        'eg007' => 'EG007EnvelopeGetDoc',
        'eg008' => 'EG008CreateTemplate',
        'eg009' => 'EG009UseTemplate',
        'eg010' => 'EG010SendBinaryDocs',
        'eg011' => 'EG011EmbeddedSending',
        'eg012' => 'EG012EmbeddedConsole',
        'eg013' => 'EG013AddDocToTemplate',
        'eg014' => 'EG014CollectPayment',
        'eg015' => 'EG015EnvelopeTabData',
        'eg016' => 'EG016SetTabValues',
        'eg017' => 'EG017SetTemplateTabValues',
        'eg018' => 'EG018EnvelopeCustomFieldData',
        'eg019' => 'EG019AccessCodeAuthentication',
        'eg020' => 'EG020SmsAuthentication',
        'eg021' => 'EG021PhoneAuthentication',
        'eg022' => 'EG022KbAuthentication',
        'eg023' => 'EG023IDVAuthentication',
        'eg024' => 'EG024PermissionCreate',
        'eg025' => 'EG025PermissionSetUserGroup',
        'eg026' => 'EG026PermissionChangeSingleSetting',
        'eg027' => 'EG027PermissionDelete',
        'eg028' => 'EG028CreateBrand',
        'eg029' => 'EG029ApplyBrandToEnvelope',
        'eg030' => 'EG030ApplyBrandToTemplate',
        'eg031' => 'EG031BulkSendEnvelopes',
    ];

    /**
     * The list of templates with examples
     */
    private const TEMPLATES = [
        "must_authenticate" => "must_authenticate.html",
        "ds_return" => "ds_return.html",
        "home"  => "home.html",
        "eg001" => "eg001_embedded_signing.html",
        "eg002" => "eg002_signing_via_email.html",
        "eg003" => "eg003_list_envelopes.html",
        "eg004" => "eg004_envelope_info.html",
        "eg005" => "eg005_envelope_recipients.html",
        "eg006" => "eg006_envelope_docs.html",
        "eg007" => "eg007_envelope_get_doc.html",
        "eg008" => "eg008_create_template.html",
        "eg009" => "eg009_use_template.html",
        "eg010" => "eg010_send_binary_docs.html",
        "eg011" => "eg011_embedded_sending.html",
        "eg012" => "eg012_embedded_console.html",
        "eg013" => "eg013_add_doc_to_template.html",
        "eg014" => "eg014_collect_payment.html",
        "eg015" => "eg015_envelope_tab_data.html",
        "eg016" => "eg016_set_tab_values.html",
        "eg017" => "eg017_set_template_tab_values.html",
        "eg018" => "eg018_envelope_custom_field_data.html",
        "eg019" => "eg019_access_code_authentication.html",
        "eg020" => "eg020_sms_authentication.html",
        "eg021" => "eg021_phone_authentication.html",
        "eg022" => "eg022_kba_authentication.html",
        "eg023" => "eg023_idv_authentication.html",
        "eg024" => "eg024_permissions_creating.html",
        "eg025" => "eg025_permissions_set_user_group.html",
        "eg026" => "eg026_permission_change_single_setting.html",
        "eg027" => "eg027_permissions_delete.html",
        "eg028" => "eg028_create_brand.html",
        "eg029" => "eg029_apply_brand_to_envelope.html",
        "eg030" => "eg030_apply_brand_to_template.html",
        "eg031" => "eg031_bulk_send.html",
    ];

    /**
     * The list of titles for each example
     */
    private const TITLES = [
        "home" => "Home--PHP Code Examples",
        "eg001" => "Embedded Signing Ceremony",
        "eg002" => "Signing via email",
        "eg003" => "List of changed envelopes",
        "eg004" => "Envelope information",
        "eg005" => "Envelope recipient information",
        "eg006" => "Envelope documents",
        "eg007" => "Envelope documents",
        "eg008" => "Create a template",
        "eg009" => "Use a template to send an envelope",
        "eg010" => "Send binary documents",
        "eg011" => "Embedded Sending",
        "eg012" => "Embedded Console",
        "eg013" => "Embedded Signing Ceremony from template and extra doc",
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
                error_reporting(E_ALL & ~E_NOTICE);
                $controller = '\Example\Controllers\Examples\\' . $this->getController($page);
                new $controller($page);
                exit();
            }
        }

        if ($page == 'must_authenticate') {
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
            $_SESSION['beenHere'] = true;
            $this->ds_logout(); // See below in oauth section
            exit();
        } elseif ($page == 'ds_return') {
            $GLOBALS['twig']->display('ds_return.html', [
                'title' => 'Returned data',
                'event' => isset($_GET['event']) ? $_GET['event'] : false,
                'envelope_id' => isset($_GET['envelope_id']) ? $_GET['envelope_id'] : false,
                'state' => isset($_GET['state']) ? $_GET['state'] : false
            ]);

            // handle eg001 being listed in project root
        } elseif ($page == 'eg001') {
            // To ignore the Notice instead of Isset on missing POST vars
            error_reporting(E_ALL & ~E_NOTICE);
            $controller = '\Example\\' .$this->getController($page);
            new $controller($page);
            exit();


        } else {
            // To ignore the Notice instead of Isset on missing POST vars
            error_reporting(E_ALL & ~E_NOTICE);
            $controller = '\Example\Controllers\Examples\eSignature\\' . $this->getController($page);
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
