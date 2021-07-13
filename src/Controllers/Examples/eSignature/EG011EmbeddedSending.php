<?php
/**
 * Example 011: Use embedded sending: Remote signer, cc; envelope has three documents
 */

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\ReturnUrlRequest;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;

class EG011EmbeddedSending extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg011";  # reference (and url) for this example

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->args = $this->getTemplateArgs();
        $this->clientService = new SignatureClientService($this->args);
        $this->routerService = new RouterService();
        parent::controller($this->eg, $this->routerService, basename(__FILE__));
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     *
     * @return void
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    public function createController(): void
    {
        $minimum_buffer_min = 3;
        $token_ok = $this->routerService->ds_token_ok($minimum_buffer_min);
        if ($token_ok) {
            # 2. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $results = $this->worker($this->args);
            if ($results) {
                # Redirect the user to the NDSE view
                # Don't use an iFrame!
                # State can be stored/recovered using the framework's session or a
                # query parameter on the returnUrl
                header('Location: ' . $results["redirect_url"]);
                exit;
            }
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }


    /**
     * Do the work of the example
     * 1. Create the envelope with "created" (draft) status
     * 2. Send the envelope
     * 3. Get the SenderView url
     *
     * @param  $args array
     * @return array ['redirect_url']
     * @throws ApiException for API problems and perhaps file access \Exception too.
     */
    # ***DS.snippet.0.start
    private function worker($args): array
    {


        # 1. Create the envelope as a draft using eg002's worker
        # Exceptions will be caught by the calling function
        # Set this createDraft session variable to true to override 
        # The default behavior of eg002 so that eg011s controller continues to function
        # This external session variable is reset on eg002 in setArguments 

        $_SESSION["createDraft"] = true;
        $eg = new EG002SigningViaEmail();
        $results = $eg->worker($args);
        $envelope_id= $results['envelope_id'];

        # 2. Create sender view
        $view_request = new ReturnUrlRequest(['return_url' => $args['ds_return_url']]);
        $envelope_api = $this->clientService->getEnvelopeApi();
        $results = $envelope_api->createSenderView($args['account_id'], $envelope_id, $view_request);

        # Switch to the Recipients / Documents view if requested by the user in the form
        $url = $results['url'];
        if ($args['starting_view'] == "recipient") {
            $url = str_replace('send=1', 'send=0', $url);
        }

        return ['envelope_id' => $envelope_id, 'redirect_url' =>  $url];
    }
    # ***DS.snippet.0.end

    /**
     * Get specific template arguments
     *
     * @return array
     */
    private function getTemplateArgs(): array
    {
        $signer_name   = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_name'  ]);
        $signer_email  = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_email' ]);
        $cc_name       = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_name'      ]);
        $cc_email      = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_email'     ]);
        $starting_view = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['starting_view']);
        $envelope_args = [
            'signer_email' => $signer_email,
            'signer_name' => $signer_name,
            'cc_email' => $cc_email,
            'cc_name' => $cc_name,
        ];
        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'starting_view' => $starting_view,
            'envelope_args' => $envelope_args,
            'ds_return_url' => $GLOBALS['app_url'] . 'index.php?page=ds_return'
        ];

        return $args;
    }
}