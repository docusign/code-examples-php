<?php


namespace Example\Services;

use DocuSign\eSign\Api\AccountsApi;
use DocuSign\eSign\Api\BulkEnvelopesApi;
use DocuSign\eSign\Api\EnvelopesApi;
use DocuSign\eSign\Api\GroupsApi;
use DocuSign\eSign\Api\TemplatesApi;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Configuration;
use DocuSign\eSign\Model\RecipientViewRequest;

class ClientService
{
    /**
     * DocuSign API Client
     */
    public $apiClient;

    /**
     * Router Service
     */
    public $routerService;

    /**
     * Create a new controller instance.
     *
     * @param $args
     * @return void
     */
    public function __construct($args)
    {
        # 2. Construct your API headers
        # Exceptions will be caught by the calling function
        $config = new Configuration();
        $config->setHost($args['base_path']);
        $config->addDefaultHeader('Authorization', 'Bearer ' . $args['ds_access_token']);
        $this->apiClient = new ApiClient($config);
        $this->routerService = new RouterService();
    }

    /**
     * Getter for the EnvelopesApi
     */
    public function getEnvelopeApi(): EnvelopesApi
    {
        return new EnvelopesApi($this->apiClient);
    }

    /**
     * Getter for the TemplatesApi
     */
    public function getTemplatesApi(): TemplatesApi
    {
        return new TemplatesApi($this->apiClient);
    }

    /**
     * Getter for the AccountsApi
     */
    public function getAccountsApi(): AccountsApi
    {
        return new AccountsApi($this->apiClient);
    }

    /**
     * Getter for the AccountsApi
     */
    public function getGroupsApi(): GroupsApi
    {
        return new GroupsApi($this->apiClient);
    }

    /**
     * Getter for the BulkEnvelopesApi
     */
    public function getBulkEnvelopesApi(): BulkEnvelopesApi
    {
        return new BulkEnvelopesApi($this->apiClient);
    }

    /**
     * Getter for the RecipientViewRequest
     */
    public function getRecipientViewRequest($authentication_method, $envelope_args): RecipientViewRequest
    {
        return new RecipientViewRequest([
            'authentication_method' => $authentication_method,
            'client_user_id' => $envelope_args['signer_client_id'],
            'recipient_id' => '1',
            'return_url' => $envelope_args['ds_return_url'],
            'user_name' => $envelope_args['signer_name'], 'email' => $envelope_args['signer_email']
        ]);
    }

    /**
     * Getter for the AccountsApi
     *
     * @param $account_id string
     * @param $envelope_id string
     * @param $recipient_view_request RecipientViewRequest
     * @return mixed - the list of Recipient Views
     */
    public function getRecipientView($account_id, $envelope_id, $recipient_view_request)
    {
        try {
            $envelope_api = $this->getEnvelopeApi();
            $results = $envelope_api->createRecipientView($account_id, $envelope_id, $recipient_view_request);
        } catch (ApiException $e) {
            $error_code = $e->getResponseBody()->errorCode;
            $error_message = $e->getResponseBody()->message;
            $GLOBALS['twig']->display('error.html', [
                    'error_code' => $error_code,
                    'error_message' => $error_message]
            );
            exit;
        }

        return $results;
    }

    /**
     * Redirect user to the error page
     *
     * @param  ApiException $e
     * @return void
     */
    public function showErrorTemplate(ApiException $e): void
    {
        $body = $e->getResponseBody();
        $GLOBALS['twig']->display('error.html', [
                'error_code' => $body->errorCode ?? unserialize($body)->errorCode,
                'error_message' => $body->message ?? unserialize($body)->message]
        );
    }

    /**
     * Redirect user to the error page
     *
     * @param $title string
     * @param $headline string
     * @param $message string
     * @param $results
     * @return void
     */
    public function showDoneTemplate($title, $headline, $message, $results = null): void
    {
        $GLOBALS['twig']->display('example_done.html', [
            'title' => $title,
            'h1' => $headline,
            'message' => $message,
            'json' => $results
        ]);
        exit;
    }

    /**
     * Redirect user to the auth page
     *
     * @param $eg
     * @return void
     */
    public function needToReAuth($eg): void
    {
        $this->routerService->flash('Sorry, you need to re-authenticate.');
        # We could store the parameters of the requested operation
        # so it could be restarted automatically.
        # But since it should be rare to have a token issue here,
        # we'll make the user re-enter the form data after
        # authentication.
        $_SESSION['eg'] = $GLOBALS['app_url'] . 'index.php?page=' . $eg;
        header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=must_authenticate');
        exit;
    }

    /**
     * Redirect user to the template page if !envelope_id
     *
     * @param $basename string
     * @param $template string
     * @param $title string
     * @param $eg string
     * @param $is_ok null|array
     * @return void
     */
    public function envelopeNotCreated($basename, $template, $title, $eg, $is_ok = null)
    {
        $conf = [
            'title' => $title,
            'source_file' => $basename,
            'source_url' => $GLOBALS['DS_CONFIG']['github_example_url'] . $basename,
            'documentation' => $GLOBALS['DS_CONFIG']['documentation'] . $eg,
            'show_doc' => $GLOBALS['DS_CONFIG']['documentation'],
        ];

        $GLOBALS['twig']->display($template, array_push($conf, $is_ok));
        exit;
    }

    /**
     *  Get the lis of the Brands
     *
     * @param  array $args
     * @return array $brands
     */
    public function getBrands($args): array
    {
        # Retrieve all brands using the AccountBrands::List
        $accounts_api = $this->getAccountsApi();
        try {
            $brands = $accounts_api->listBrands($args['account_id']);
        } catch (ApiException $e) {
            $this->showErrorTemplate($e);
            exit;
        }

        return $brands['brands'];
    }

    /**
     *  Get the lis of the Permission Profiles
     *
     * @param  array $args
     * @return array $brands
     */
    public function getPermissionsProfiles($args): array
    {
        # Retrieve all brands using the AccountBrands::List
        $accounts_api = $this->getAccountsApi();
        try {
            $brands = $accounts_api->listPermissions($args['account_id']);
        } catch (ApiException $e) {
            $this->showErrorTemplate($e);
            exit;
        }

        return $brands['permission_profiles'];
    }

    /**
     *  Get the lis of the Groups
     *
     * @param  array $args
     * @return array $brands
     */
    public function getGroups($args): array
    {
        # Retrieve all Groups using the GroupInformation::List
        $accounts_api = $this->getGroupsApi();
        try {
            $brands = $accounts_api->listGroups($args['account_id']);
        } catch (ApiException $e) {
            $this->showErrorTemplate($e);
            exit;
        }

        return $brands['groups'];
    }

    /**
     * Creates a customized html document for the envelope
     *
     * @param  $args array
     * @return string -- the html document
     */
    public function createDocumentForEnvelope(array $args): string
    {
        return <<< heredoc
    <!DOCTYPE html>
    <html>
        <head>
          <meta charset="UTF-8">
        </head>
        <body style="font-family:sans-serif;margin-left:2em;">
        <h1 style="font-family: 'Trebuchet MS', Helvetica, sans-serif;
color: darkblue;margin-bottom: 0;">World Wide Corp</h1>
        <h2 style="font-family: 'Trebuchet MS', Helvetica, sans-serif;
margin-top: 0px;margin-bottom: 3.5em;font-size: 1em;
color: darkblue;">Order Processing Division</h2>
        <h4>Ordered by {$args['signer_name']}</h4>
        <p style="margin-top:0em; margin-bottom:0em;">Email: {$args['signer_email']}</p>
        <p style="margin-top:0em; margin-bottom:0em;">Copy to: {$args['cc_name']}, {$args['cc_email']}</p>
        <p style="margin-top:3em;">
  Candy bonbon pastry jujubes lollipop wafer biscuit biscuit. Topping brownie sesame snaps sweet roll pie. Croissant danish biscuit soufflé caramels jujubes jelly. Dragée danish caramels lemon drops dragée. Gummi bears cupcake biscuit tiramisu sugar plum pastry. Dragée gummies applicake pudding liquorice. Donut jujubes oat cake jelly-o. Dessert bear claw chocolate cake gummies lollipop sugar plum ice cream gummies cheesecake.
        </p>
        <!-- Note the anchor tag for the signature field is in white. -->
        <h3 style="margin-top:3em;">Agreed: <span style="color:white;">**signature_1**/</span></h3>
        </body>
    </html>
heredoc;
    }
}