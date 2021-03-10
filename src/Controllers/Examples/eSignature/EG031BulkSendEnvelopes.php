<?php

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\BulkSendingCopy;
use DocuSign\eSign\Model\BulkSendingCopyRecipient;
use DocuSign\eSign\Model\BulkSendingList;
use DocuSign\eSign\Model\BulkSendRequest;
use DocuSign\eSign\Model\CustomFields;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use DocuSign\eSign\Model\TextCustomField;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;

class EG031BulkSendEnvelopes extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg031"; # Reference (and URL) for this example

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
     * @throws ApiException for API problems and perhaps file access \Exception, too
     */
    public function createController(): void
    {
        $minimum_buffer_min = 3;
        if ($this->routerService->ds_token_ok($minimum_buffer_min)) {
            # 1. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $results = json_decode($this->worker($this->args), true);

            if ($results) {
                # That need an envelope_id
                $this->clientService->showDoneTemplate(
                    "Bulk sending envelopes to multiple recipients",
                    "Bulk sending envelopes to multiple recipients",
                    "The envelope has been sent to recipients!<br/> Batch id: {$results['batchId']}"
                );
            }
        } else {
            $this->clientService->needToReAuth($this->eg);
        }
    }

    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     *
     * @param  $args array
     * @return string
     * @throws ApiException for API problems and perhaps file access \Exception, too
     */
    # ***DS.snippet.0.start
    public function worker($args): string
    {

        $bulk_envelopes_api = $this->clientService->getBulkEnvelopesApi();
        $envelope_api = $this->clientService->getEnvelopeApi();

        # Step 3. Submit a bulk list
        $bulk_sending_list = $this->createBulkSendingList($args["signers"]);
        $bulk_list = $bulk_envelopes_api->createBulkSendList($args["account_id"], $bulk_sending_list);
        $bulk_list_id = $bulk_list["list_id"];

        # Step 4. Create an envelope
        $envelope_definition = $this->make_envelope($args);
        $envelope = $envelope_api->createEnvelope($args["account_id"], $envelope_definition);
        $envelope_id = $envelope["envelope_id"];

        # Step 5. Attach your bulk list ID to the envelope
        $text_custom_fields = new TextCustomField([
            "name" => "mailingListId",
            "required" => "false",
            "show" => "false",
            "value" => $bulk_list_id
        ]);

        $custom_fields = new CustomFields([
            "list_custom_fields" => [],
            "text_custom_fields" => [$text_custom_fields]
        ]);

        $envelope_api->createCustomFields($args["account_id"], $envelope_id, $custom_fields);

        # Step 6. Add placeholder recipients
        $signer = new Signer([
            'name' => 'Multi Bulk Recipient::signer',
            'email' => 'multiBulkRecipients-signer@docusign.com',
            'role_name' => "signer",
            'note' => "",
            'routing_order' => '1',
            'status' => 'created',
            'delivery_method' => 'Email',
            'recipient_id' => '12', # Represents your {RECIPIENT_ID}
            'recipient_type' => "signer"
        ]);

        $cc = new Signer([
            'name' => 'Multi Bulk Recipient::cc',
            'email' => 'multiBulkRecipients-cc@docusign.com',
            'role_name' => "cc",
            'note' => "",
            'routing_order' => '1',
            'status' => 'created',
            'delivery_method' => 'Email',
            'recipient_id' => '13', # Represents your {RECIPIENT_ID}
            'recipient_type' => "signer"
        ]);

        $recipients = new Recipients(['signers' => [$signer, $cc]]);
        $envelope_api->createRecipient($args['account_id'], $envelope_id, $recipients);

        # Step 7. Initiate bulk send
        $bulk_send_request = new BulkSendRequest(['envelope_or_template_id' => $envelope_id]);

        $batch = $bulk_envelopes_api->createBulkSendRequest(
            $args["account_id"],
            $bulk_list_id,
            $bulk_send_request
        );

        # Step 8. Confirm successful batch send
        # Exceptions will be caught by the calling function
        try {
            $results = $bulk_envelopes_api->getBulkSendBatchStatus(
                $args['account_id'],
                $batch['batch_id']
            );
        } catch (ApiException $e) {
            $this->clientService->showErrorTemplate($e);
            exit;
        }

        return $results;
    }

    /**
     *  Create bulk sending list
     *
     * @param  $signers array
     * @return BulkSendingList -- returns a bulk sending list
     */
    private function createBulkSendingList($signers): BulkSendingList
    {
        # 1. Create recipient objects with signers
        # 2. Create recipient objects with ccs
        # 3. Create bulk copies objects
        # 4. Create the bulk sending list object

        $bulk_copies = [];
        foreach ($signers as $signer) {
            $recipient_1 = new BulkSendingCopyRecipient([
                "role_name" => "signer",
                "tabs" => [],
                "name" => $signer["signer_name"],
                "email" => $signer["signer_email"]
            ]);

            $recipient_2 = new BulkSendingCopyRecipient([
                "role_name" => "cc",
                "tabs" => [],
                "name" => $signer["cc_name"],
                "email" => $signer["cc_email"]
            ]);

            $bulk_copy = new BulkSendingCopy([
                "recipients" => [$recipient_1, $recipient_2],
                "custom_fields" => []
            ]);

            array_push($bulk_copies, $bulk_copy);
        }

        $bulk_sending_list = new BulkSendingList(["name" => "sample"]);
        $bulk_sending_list->setBulkCopies($bulk_copies);

        return $bulk_sending_list;
    }

    /**
     *  Creates envelope definition
     *  Parameters for the envelope: signer_email, signer_name, signer_client_id
     *
     * @param  $args array
     * @return EnvelopeDefinition -- returns an envelope definition
     */
    private function make_envelope($args)
    {
        # Document 1 (PDF) has tag /sn1/
        #
        # The envelope has one recipient.
        # recipient 1 - signer
        #
        # Read the file
        $content_bytes = file_get_contents(self::DEMO_DOCS_PATH . $GLOBALS['DS_CONFIG']['doc_pdf']);
        $base64_file_content = base64_encode($content_bytes);

        # Create the document model
        $document = new Document([ # Create the DocuSign document object
            'document_base64' => $base64_file_content,
            'name' => 'Example document', # Can be different from actual file name
            'file_extension' => 'pdf', # Many different document types are accepted
            'document_id' => 1 # A label used to reference the doc
        ]);

        # Create the signer recipient model
        $signer = new Signer([ # The signer
            'email' => $args['signers'][0]['signer_email'], 'name' => $args['signers'][0]['signer_name'],
            'recipient_id' => "1", 'routing_order' => "1",
        ]);

        # Create a SignHere tab (field on the document)
        $sign_here = new SignHere([ # DocuSign SignHere field/tab
            'anchor_string' => '/sn1/', 'anchor_units' => 'pixels',
            'anchor_y_offset' => '10', 'anchor_x_offset' => '20'
        ]);

        # Add the tabs model (including the sign_here tab) to the signer
        # The Tabs object takes arrays of the different field/tab types
        $signer->settabs(new Tabs(['sign_here_tabs' => [$sign_here]]));

        # Next, create the top-level envelope definition and populate it
        $envelope_definition = new EnvelopeDefinition([
            'email_subject' => "Please sign this document sent from the PHP SDK",
            'documents' => [$document],
            # The Recipients object takes arrays for each recipient type
            'recipients' => new Recipients(['signers' => [$signer]]),
            'status' => "sent" # Requests that the envelope be created and sent
        ]);

        return $envelope_definition;
    }

    # ***DS.snippet.0.end

    /**
     * Get specific template arguments
     *
     * @return array
     */
    private function getTemplateArgs(): array
    {
        $signer_name_1  = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_name_1']);
        $signer_email_1 = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_email_1']);
        $cc_name_1      = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_name_1']);
        $cc_email_1     = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_email_1']);
        $signer_name_2  = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_name_2']);
        $signer_email_2 = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_email_2']);
        $cc_name_2      = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_name_2']);
        $cc_email_2     = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_email_2']);
        $signers = [
            [
                'signer_email' => $signer_email_1,
                'signer_name' => $signer_name_1,
                'cc_email' => $cc_email_1,
                'cc_name' => $cc_name_1
            ],
            [
                'signer_email' => $signer_email_2,
                'signer_name' => $signer_name_2,
                'cc_email' => $cc_email_2,
                'cc_name' => $cc_name_2
            ]
        ];
        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'signers' => $signers
        ];

        return $args;
    }
}
