<?php

namespace Example\Controllers\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\Checkbox;
use DocuSign\eSign\Model\ConditionalRecipientRule;
use DocuSign\eSign\Model\ConditionalRecipientRuleCondition;
use DocuSign\eSign\Model\ConditionalRecipientRuleFilter;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\RecipientGroup;
use DocuSign\eSign\Model\RecipientOption;
use DocuSign\eSign\Model\RecipientRouting;
use DocuSign\eSign\Model\RecipientRules;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use DocuSign\eSign\Model\Workflow;
use DocuSign\eSign\Model\WorkflowStep;
use Example\Controllers\eSignBaseController;
use Example\Services\SignatureClientService;
use Example\Services\RouterService;

class EG034UseConditionalRecipients extends eSignBaseController
{
    /** signatureClientService */
    private $clientService;

    /** RouterService */
    private $routerService;

    /** Specific template arguments */
    private $args;

    private $eg = "eg034"; # Reference (and URL) for this example

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
            $envelope_id = $this->worker($this->args);

            if ($envelope_id) {
                # That need an envelope_id
                $this->clientService->showDoneTemplate(
                    "Use conditional recipients",
                    "Use conditional recipients",
                    "Envelope ID {$envelope_id} with the conditional
                            routing criteria has been created and sent to the first recipient!"
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
        $envelope_args = $args['envelope_args'];
        $envelope_api = $this->clientService->getEnvelopeApi();

        # Step 3 Start
        $envelope_definition = $this->make_envelope($envelope_args);
        # Step 3 End

        # Step 4 Start
        $envelope = $envelope_api->createEnvelope($args["account_id"], $envelope_definition);
        $envelope_id = $envelope["envelope_id"];
        # Step 4 End
        
        return  $envelope_id;
    }

    /**
     *  Creates envelope definition
     *  Parameters for the envelope: signer_email, signer_name, signer_client_id
     *
     * @param  $args array
     * @return EnvelopeDefinition -- returns an envelope definition
     */
    private function make_envelope($envelope_args)
    {
        # The envelope has two recipients
        # Recipient 1 - signer1
        # Recipient 2 - signer2
        # The envelope will be sent first to the signer1
        # After it is signed, a signature workflow will be paused
        # After resuming (unpause) the signature workflow will send to the second recipient

        # Create the top-level envelope definition
        $envelope_definition = new EnvelopeDefinition([
            'email_subject' => "ApproveIfChecked",
        ]);

        # Read the file
        $content_bytes = file_get_contents(self::DEMO_DOCS_PATH . $GLOBALS['DS_CONFIG']['doc_txt']);
        $base64_file_content = base64_encode($content_bytes);
        # Create the document model
        $document = new Document([ # Create the DocuSign document object
            'document_base64' => $base64_file_content,
            'name' => 'Welcome', # Can be different from actual file name
            'file_extension' => 'txt', # Many different document types are accepted
            'document_id' => "1" # A label used to reference the doc
        ]);

        # The order in the docs array determines the order in the envelope.
        $envelope_definition->setDocuments([$document, ]);

        # Create the signer recipient models
        # routing_order (lower means earlier) determines the order of deliveries
        # to the recipients.
        $signer1 = new Signer([ # The signer1
            'email' => $envelope_args['signer1_email'],
            'name' => $envelope_args['signer1_name'],
            'recipient_id' => "1",
            'routing_order' => "1",
            'role_name' => "Purchaser",
        ]);

        $signer2 = new Signer([ # The signer2
            'email' => "placeholder@example.com",
            'name' => "Approver",
            'recipient_id' => "2",
            'routing_order' => "2",
            'role_name' => "Approver"
        ]);

        # Create SignHere fields (also known as tabs) on the documents.
        $sign_here1 = new SignHere([
            'document_id' => "1",
            'page_number' => "1",
            'name' => "SignHere",
            'tab_label' => "PurchaserSignature",
            'x_position' => "200",
            'y_position' => "200",
        ]);

        $sign_here2 = new SignHere([
            'document_id' => "1",
            'page_number' => "1",
            'name' => "SignHere",
            'tab_label' => "ApproverSignature",
            'recipient_id' => "2",
            'x_position' => "300",
            'y_position' => "200",
        ]);

        # Create checkbox field on the documents
        $checkbox = new Checkbox([
            'document_id' => "1",
            'page_number' => "1",
            'name' => "ClickToApprove",
            'selected' => "false",
            'tab_label' => "ApproveWhenChecked",
            'x_position' => "50",
            'y_position' => "50",
        ]);

        # Add the tabs model (including the sign_here tabs) to the signer
        # The Tabs object takes arrays of the different field/tab types
        $signer1->setTabs(
            new Tabs([
                'sign_here_tabs' => [$sign_here1, ],
                'checkbox_tabs' => [$checkbox, ]
            ])
        );

        $signer2->setTabs(
            new Tabs([
                'sign_here_tabs' => [$sign_here2, ],
            ])
        );

        # Add the recipients to the envelope object
        $env_recipients = new Recipients([
            'signers' => [$signer1, $signer2, ],
        ]) ;
        $envelope_definition->setRecipients($env_recipients);

        # Create recipientOption models
        $signer_2a = new RecipientOption([ # The signer2
            'email' => $envelope_args['signer_2a_email'],
            'name' => $envelope_args['signer1_2a_name'],
            'role_name' => "Signer when not checked",
            'recipient_label' => "signer2a",
        ]);

        $signer_2b = new RecipientOption([ # The signer2
            'email' => $envelope_args['signer_2b_email'],
            'name' => $envelope_args['signer1_2b_name'],
            'role_name' => "Signer when checked",
            'recipient_label' => "signer2b",
        ]);

        $recipients = [$signer_2a, $signer_2b, ];

        # Create recipientGroup model
        $recipient_group = new RecipientGroup([
            'group_name' => "Approver",
            'group_message' => "Members of this group approve a workflow",
            'recipients' => $recipients,
        ]);

        # Create conditionalRecipientRuleFilter models
        $filter1 = new ConditionalRecipientRuleFilter([
            'scope' => "tabs",
            'recipient_id' => "1",
            'tab_id' => "ApprovalTab",
            'operator' => "equals",
            'value' => "false",
            'tab_type' => "checkbox",
            'tab_label' => "ApproveWhenChecked"
        ]);

        $filter2 = new ConditionalRecipientRuleFilter([
            'scope' => "tabs",
            'recipient_id' => "1",
            'tab_id' => "ApprovalTab",
            'operator' => "equals",
            'value' => "true",
            'tab_type' => 'checkbox',
            'tab_label' => "ApproveWhenChecked"
        ]);

        # Create conditionalRecipientRuleCondition models
        $condition1 = new ConditionalRecipientRuleCondition([
            'filters' => [$filter1, ],
            'order' => "1",
            'recipient_label' => "signer2a"
        ]);
        $condition2 = new ConditionalRecipientRuleCondition([
            'filters' => [$filter2, ],
            'order' => "2",
            'recipient_label' => "signer2b"
        ]);
        $conditions = [$condition1, $condition2];

        # Create conditionalRecipientRule model
        $conditional_recipient = new ConditionalRecipientRule([
            'conditions' => $conditions,
            'recipient_group' => $recipient_group,
            'recipient_id' => "2",
            'order' => "0",

        ]);

        # Create recipientRules model
        $rules = new RecipientRules(['conditional_recipients'=> [$conditional_recipient, ]]);
        $recipient_routing = new RecipientRouting(['rules' => $rules]);

        # Create a workflow model
        $workflow_step = new WorkflowStep([
            'action' => "pause_before",
            'trigger_on_item' => "routing_order",
            'item_id' => "2",
            'status' => "pending",
            'recipient_routing' => $recipient_routing,
        ]);

        # Create a workflow model
        $workflow = new Workflow([
            'workflow_steps' => [$workflow_step, ],
        ]);
        # Add the workflow to the envelope object
        $envelope_definition->setWorkflow($workflow);


        # Request that the envelope be sent by setting |status| to "sent"
        # To request that the envelope be created as a draft, set to "created"
        $envelope_definition->setStatus($envelope_args['status']);
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
        $signer1_name  = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer1_name']);
        $signer1_email = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer1_email']);
        $signer_2a_name = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_2a_name']);
        $signer_2a_email = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_2a_email']);
        $signer_2b_name = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_2b_name']);
        $signer_2b_email = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_2b_email']);
        $envelope_args = [
            'signer1_email' => $signer1_email,
            'signer1_name' => $signer1_name,
            'signer_2a_email' => $signer_2a_email,
            'signer_2a_name' => $signer_2a_name,
            'signer_2b_email' => $signer_2b_email,
            'signer_2b_name' => $signer_2b_name,
            'status' => "Sent",
        ];
        $args = [
            'account_id' => $_SESSION['ds_account_id'],
            'base_path' => $_SESSION['ds_base_path'],
            'ds_access_token' => $_SESSION['ds_access_token'],
            'envelope_args' => $envelope_args
        ];

        return $args;
    }
}
