<?php

namespace QuickACG;

use Example\Services\CodeGrantService;
use Example\Services\IRouterService;

class RouterService implements IRouterService
{
    private const CONTROLLER = [
        'eg001' => 'EG001EmbeddedSigning'
    ];

    private const TEMPLATES = [
        'eg001' => 'esignature/quickEmbeddedSigning.html'
    ];

    private const TITLES = [
        'eg001' => 'Use embedded signing'
    ];

    private const SESSION_VALUES = [
        'ds_access_token',
        'ds_refresh_token',
        'ds_user_email',
        'ds_user_name',
        'ds_expiration',
        'ds_account_id',
        'ds_account_name',
        'ds_base_path',
        'envelope_id',
        'eg',
        'envelope_documents',
        'template_id',
        'api_type',
        'auth_service'
    ];

    public $authService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // To ignore the Notice instead of Isset on missing POST vars
        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

        $this->authService = new CodeGrantService();
    }

    /**
     * Page router
     */
    public function router(): void
    {
        $page = $_GET['page'] ?? 'eg001';

        switch ($page) {
            case 'select_api':
                $this->ds_login();
                break;
            case 'ds_callback':
                $this->ds_callback(); // See below in oauth section
                break;
            case 'ds_return':
                header('Location: ' . '/public', true);
                break;
            default:
                error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
                $controller = '\Example\\' . $this->getController($page);
                new $controller($page);
                break;
        }
    }

    /**
     * @param int $buffer_min buffer time needed in minutes
     * @return boolean $ok true iff the user has an access token that will be good for another buffer min
     */
    function ds_token_ok(int $buffer_min = 10): bool
    {
        $areTokenAndExprirationSet = isset($_SESSION['ds_access_token']) && isset($_SESSION['ds_expiration']);

        return $areTokenAndExprirationSet && (($_SESSION['ds_expiration'] - ($buffer_min * 60)) > time());
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
    function ds_logout_internal(): void
    {
        foreach (self::SESSION_VALUES as &$sessionValue) {
            if (isset($_SESSION[$sessionValue])) {
                unset($_SESSION[$sessionValue]);
            }
        }
    }

    /**
     * DocuSign login handler
     */
    function ds_login(): void
    {
        $this->authService->login();
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
        $tempAPIType = $_SESSION['api_type'];
        $tempGrant = $_SESSION['auth_service'];
        $this->ds_logout_internal();

        // Workaround for ACG apiTypePicker
        $_SESSION['api_type'] = $tempAPIType;
        $_SESSION['auth_service'] = $tempGrant;
        $this->authService->authCallback($redirectUrl);
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
     * Checker for the CSRF token
     */
    function check_csrf(): void
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
        return self::TITLES[$eg];
    }
}
