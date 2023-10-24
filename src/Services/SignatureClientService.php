<?php

namespace DocuSign\Services;

use DocuSign\eSign\Api\AccountsApi;
use DocuSign\eSign\Api\BulkEnvelopesApi;
use DocuSign\eSign\Api\EnvelopesApi;
use DocuSign\eSign\Api\GroupsApi;
use DocuSign\eSign\Api\TemplatesApi;
use DocuSign\eSign\Api\UsersApi;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Configuration;
use DocuSign\eSign\Model\RecipientViewRequest;
use QuickACG\RouterService as QuickRouterService;
use DocuSign\Controllers\BaseController;

class SignatureClientService
{
    /**
     * DocuSign API Client
     */
    public ApiClient $apiClient;

    /**
     * Router Service
     */
    public IRouterService $routerService;

    /**
     * Create a new controller instance.
     *
     * @param $args
     * @return void
     */
    public function __construct($args)
    {
        # Construct your API headers
        # Exceptions will be caught by the calling function
        #ds-snippet-start:eSignPHPStep2
        $config = new Configuration();
        $config->setHost($args['base_path']);
        $config->addDefaultHeader('Authorization', 'Bearer ' . $args['ds_access_token']);
        $this->apiClient = new ApiClient($config);
        #ds-snippet-end:eSignPHPStep2
        $this->routerService = isset($GLOBALS['DS_CONFIG']) && $GLOBALS['DS_CONFIG']['quickACG'] === "true"
            ? new QuickRouterService()
            : new RouterService();
    }

    /**
     * Getter for the TemplatesApi
     */
    public function getTemplatesApi(): TemplatesApi
    {
        return new TemplatesApi($this->apiClient);
    }

    /**
     * Getter for the UsersApi
     */
    public function getUsersApi(): UsersApi
    {
        return new UsersApi($this->apiClient);
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
    #ds-snippet-start:eSign39Step4
    public function getRecipientViewRequest($authentication_method, $envelope_args): RecipientViewRequest
    {
        return new RecipientViewRequest(
            [
                'authentication_method' => $authentication_method,
                'client_user_id' => $envelope_args['signer_client_id'],
                'recipient_id' => '1',
                'return_url' => $envelope_args['ds_return_url'],
                'user_name' => $envelope_args['signer_name'],
                'email' => $envelope_args['signer_email']
            ]
        );
    }
    #ds-snippet-end:eSign39Step4

    /**
     * Getter for the AccountsApi
     *
     * @param $account_id string
     * @param $envelope_id string
     * @param $recipient_view_request RecipientViewRequest
     * @return \DocuSign\eSign\Model\ViewUrl - the list of Recipient Views
     */

    # Step 4 Start (inPersonSigning)
    public function getRecipientView(
        string $account_id,
        string $envelope_id,
        RecipientViewRequest $recipient_view_request
    ): \DocuSign\eSign\Model\ViewUrl {
        try {
            $envelope_api = $this->getEnvelopeApi();
            $viewUrl = $envelope_api->createRecipientView($account_id, $envelope_id, $recipient_view_request);
        } catch (ApiException $e) {
            $error_code = $e->getResponseBody()->errorCode;
            $error_message = $e->getResponseBody()->message;
            if ($error_code == "WORKFLOW_UPDATE_RECIPIENTROUTING_NOT_ALLOWED") {
                $GLOBALS['twig']->display(
                    'error_eg34.html',
                    [
                        'error_code' => $error_code,
                        'error_message' => $error_message,
                        'common_texts' => ManifestService::getCommonTexts()
                    ]
                );
            } else {
                $GLOBALS['twig']->display(
                    'error.html',
                    [
                        'error_code' => $error_code,
                        'error_message' => $error_message,
                        'common_texts' => ManifestService::getCommonTexts()
                    ]
                );
            }
            exit;
        }

        return $viewUrl;
    }

    # Step 4 end

    /**
     * Getter for the EnvelopesApi
     */
    public function getEnvelopeApi(): EnvelopesApi
    {
        return new EnvelopesApi($this->apiClient);
    }

    /**
     * Redirect user to results page
     *
     * @param $codeExampleText array
     * @param null $results
     * @param $message string
     * @return void
     */
    public function showDoneTemplateFromManifest(array $codeExampleText, $results = null, string $message = null): void
    {
        if ($message == null) {
            $message = $codeExampleText["ResultsPageText"];
        }

        $GLOBALS['twig']->display('example_done.html', [
            'title' => $codeExampleText["ExampleName"],
            'h1' => $codeExampleText["ExampleName"],
            'message' => $message,
            'json' => $results,
            'common_texts' => ManifestService::getCommonTexts()
        ]);
        exit;
    }

    /**
     * Redirect user to the 'success' page
     *
     * @param $title string
     * @param $headline string
     * @param $message string
     * @param $results
     * @return void
     */
    public function showDoneTemplate(string $title, string $headline, string $message, $results = null, $redirect = null): void
    {
        $GLOBALS['twig']->display(
            'example_done.html',
            [
                'title' => $title,
                'h1' => $headline,
                'message' => $message,
                'json' => $results,
                'common_texts' => ManifestService::getCommonTexts(),
                'redirect' => $redirect
            ]
        );
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
        header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=' . BaseController::LOGIN_REDIRECT);
        exit;
    }

    /**
     * Redirect user to the template page if !envelope_id
     *
     * @param $basename string
     * @param $template string
     * @param $title string
     * @param $eg string
     * @param $is_ok array|null
     * @return void
     */
    public function envelopeNotCreated(string $basename, string $template, string $title, string $eg, array $is_ok = null): void
    {
        $conf = [
            'title' => $title,
            'source_file' => $basename,
            'source_url' => $GLOBALS['DS_CONFIG']['github_example_url'] . $basename,
            'documentation' => $GLOBALS['DS_CONFIG']['documentation'] . $eg,
            'show_doc' => $GLOBALS['DS_CONFIG']['documentation'],
            'common_texts' => ManifestService::getCommonTexts()
        ];

        $GLOBALS['twig']->display($template, array_push($conf, $is_ok));
        exit;
    }

    /**
     *  Get the lis of the Brands
     *
     * @param array $args
     * @return array $brands
     */
    public function getBrands(array $args): array
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
     * Getter for the AccountsApi
     */
    public function getAccountsApi(): AccountsApi
    {
        return new AccountsApi($this->apiClient);
    }

    /**
     * Redirect user to the error page
     *
     * @param ApiException $e
     * @return void
     */
    public function showErrorTemplate(ApiException $e, string $fixingInstructions = null): void
    {
        $body = $e->getResponseBody($fixingInstructions);

        $GLOBALS['twig']->display(
            'error.html',
            [
                'error_code' => $body->errorCode ?? unserialize($body)->errorCode ?? $e->getMessage(),
                'error_message' => $body->message ?? unserialize($body)->message,
                'fixing_instructions' => $fixingInstructions,
                'common_texts' => ManifestService::getCommonTexts()
            ]
        );
    }

    /**
     *  Get the lis of the Permission Profiles
     *
     * @param array $args
     * @return array $brands
     */
    public function getPermissionsProfiles(array $args): array
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
     * @param array $args
     * @return array $brands
     */
    public function getGroups(array $args): array
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
     *  Get the email address of the authenticated user
     *
     * @param string $accessToken
     * @return string $emailAdress
     */
    public function getAuthenticatedUserEmail(string $accessToken): string
    {
        $this->apiClient->getOAuth()->setOAuthBasePath($GLOBALS['JWT_CONFIG']['authorization_server']);
        $info = $this->apiClient->getUserInfo($accessToken);

        return $info[0]['email'];
    }

    /**
     *  Get the name of the authenticated user
     *
     * @param string $accessToken
     * @return string $emailAdress
     */
    public function getAuthenticatedUserName(string $accessToken): string
    {
        $this->apiClient->getOAuth()->setOAuthBasePath($GLOBALS['JWT_CONFIG']['authorization_server']);
        $info = $this->apiClient->getUserInfo($accessToken);

        return $info[0]['accounts'][0]['account_name'];
    }

    /**
     * Getter for the AccountsApi
     */
    public function getGroupsApi(): GroupsApi
    {
        return new GroupsApi($this->apiClient);
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
        <p style="margin-top:3em; margin-bottom:0em;">
            Item: <b> {$args['item']} </b>, quantity: <b> {$args['quantity']} </b> at market price.
        </p>
        <p style="margin-top:3em;">
  Candy bonbon pastry jujubes lollipop wafer biscuit biscuit. Topping brownie sesame snaps sweet roll pie. 
  Croissant danish biscuit soufflé caramels jujubes jelly. Dragée danish caramels lemon drops dragée. Gummi bears 
  cupcake biscuit tiramisu sugar plum pastry. Dragée gummies applicake pudding liquorice. Donut jujubes oat cake jelly-o. 
  Dessert bear claw chocolate cake gummies lollipop sugar plum ice cream gummies cheesecake.
        </p>
        <!-- Note the anchor tag for the signature field is in white. -->
        <h3 style="margin-top:3em;">Agreed: <span style="color:white;">**signature_1**/</span></h3>
        </body>
    </html>
heredoc;
    }
}
