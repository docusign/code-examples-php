<?php

namespace Example\Services\Examples\eSignature;

use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\EnvelopeSummary;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use DocuSign\eSign\Model\Workflow;
use DocuSign\eSign\Model\WorkflowStep;

class PauseSignatureWorkflowService
{
    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     *
     * @param  $args array
     * @param $clientService
     * @param $demoDocsPath
     * @return object
     */
    public static function pauseSignatureWorkflow(array $args, $clientService, $demoDocsPath): EnvelopeSummary
    {
        #ds-snippet-start:eSign32Step3
        $envelope_args = $args['envelope_args'];
        $envelope_api = $clientService->getEnvelopeApi();
        $envelope_definition = PauseSignatureWorkflowService::make_envelope($envelope_args, $demoDocsPath);
        #ds-snippet-end:eSign32Step3

        #ds-snippet-start:eSign32Step4
        $envelope = $envelope_api->createEnvelope($args["account_id"], $envelope_definition);
        #ds-snippet-end:eSign32Step4

        return $envelope;
    }

    /**
     *  Creates envelope definition
     *  Parameters for the envelope: signer_email, signer_name, signer_client_id
     *
     * @param  $envelope_args array
     * @param $demoDocsPath
     * @return EnvelopeDefinition -- returns an envelope definition
     */
    #ds-snippet-start:eSign32Step3B
    public static function make_envelope(array $envelope_args, $demoDocsPath): EnvelopeDefinition
    {
        # The envelope has two recipients
        # Recipient 1 - signer1
        # Recipient 2 - signer2
        # The envelope will be sent first to the signer1
        # After it is signed, a signature workflow will be paused
        # After resuming (unpause) the signature workflow will send to the second recipient

        # Create the top-level envelope definition
        $envelope_definition = new EnvelopeDefinition([
            'email_subject' => "EnvelopeWorkflowTest",
        ]);

        # Read the file
        $content_bytes = file_get_contents($demoDocsPath . $GLOBALS['DS_CONFIG']['doc_txt']);
        $base64_file_content = base64_encode($content_bytes);

        # Create the document model
        $document = new Document([ # Create the DocuSign document object
            'document_base64' => $base64_file_content,
            'name' => 'Example document', # Can be different from actual file name
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
        ]);

        $signer2 = new Signer([ # The signer2
            'email' => $envelope_args['signer2_email'],
            'name' => $envelope_args['signer2_name'],
            'recipient_id' => "2",
            'routing_order' => "2",
        ]);

        # Create SignHere fields (also known as tabs) on the documents.
        $sign_here1 = new SignHere([
            'document_id' => "1",
            'page_number' => "1",
            'tab_label' => "Sign Here",
            'x_position' => "200",
            'y_position' => "200",
        ]);

        $sign_here2 = new SignHere([
            'document_id' => "1",
            'page_number' => "1",
            'tab_label' => "Sign Here",
            'x_position' => "300",
            'y_position' => "200",
        ]);

        # Add the tabs model (including the sign_here tabs) to the signer
        # The Tabs object takes arrays of the different field/tab types
        $signer1->setTabs(
            new Tabs([
                'sign_here_tabs' => [$sign_here1, ],
            ])
        );

        $signer2->setTabs(
            new Tabs([
                'sign_here_tabs' => [$sign_here2, ],
            ])
        );

        # Add the recipients to the envelope object
        $recipients = new Recipients([
            'signers' => [$signer1, $signer2, ],
        ]) ;
        $envelope_definition->setRecipients($recipients);

        # Create a workflow model
        # Signature workflow will be paused after it is signed by the first signer
        $workflow_step = new WorkflowStep([
            'action' => "pause_before",
            'trigger_on_item' => "routing_order",
            'item_id' => "2",
        ]);
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
    #ds-snippet-end:eSign32Step3B
}
