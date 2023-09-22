<?php

namespace DocuSign\Services\Examples\eSignature;

use DocuSign\eSign\Client\ApiException;
use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\Recipients;
use DocuSign\eSign\Model\Signer;
use DocuSign\eSign\Model\SignHere;
use DocuSign\eSign\Model\Tabs;
use DocuSign\Services\SignatureClientService;
use DocuSign\eSign\Model\RecipientIdentityPhoneNumber;
use DocuSign\eSign\Model\RecipientIdentityInputOption;
use DocuSign\eSign\Model\RecipientIdentityVerification;

class CFREmbeddedSigningService
{
    /**
     * Do the work of the example
     * 1. Create the envelope request object
     * 2. Send the envelope
     * 3. Create the Recipient View request object
     * 4. Obtain the recipient_view_url for the embedded signing
     *
     * @param  $args array
     * @param SignatureClientService $clientService
     * @return array ['redirect_url']
     */
    # ***DS.snippet.0.start
    public static function worker(array $args, SignatureClientService $clientService, string $demoPath): array
    {


        // Obtain your workflowID

        $accounts_api = $clientService->getAccountsApi();
        $accounts_response = $accounts_api->getAccountIdentityVerification($_SESSION['ds_account_id']);
        $workflows_data = $accounts_response->getIdentityVerification();
        foreach ($workflows_data as $workflow) {
            if ($workflow['default_name'] == 'SMS for access & signatures') {
                $args['envelope_args']['workflow_id'] = $workflow['workflow_id'];
            }
        };

        if (!isset($args['envelope_args']['workflow_id'])) {
            $clientService->showErrorTemplate(new ApiException("IDENTITY_WORKFLOW_INVALID_ID"));
        }

        # Create the envelope request object
        $envelope_definition = CFREmbeddedSigningService::makeEnvelope($args['envelope_args'], $demoPath);


        $envelope_api = $clientService->getEnvelopeApi();

        # Call Envelopes::create API method
        # Exceptions will be caught by the calling function

        // var_dump($envelope_definition);
        //die;

        try {
            $envelopeSummary = $envelope_api->createEnvelope($args['account_id'], $envelope_definition);
        } catch (ApiException $e) {
            $clientService->showErrorTemplate($e);
            exit;
        }
        $envelope_id = $envelopeSummary->getEnvelopeId();

        # 3. Create the Recipient View request object
        $authentication_method = 'None'; # How is this application authenticating
        # the signer? See the `authentication_method' definition
        # https://developers.docusign.com/docs/esign-rest-api/reference/envelopes/envelopeviews/createrecipient/
        $recipient_view_request = $clientService->getRecipientViewRequest(
            $authentication_method,
            $args["envelope_args"]
        );

        # 4. Obtain the recipient_view_url for the embedded signing
        # Exceptions will be caught by the calling function
        $viewUrl = $clientService->getRecipientView($args['account_id'], $envelope_id, $recipient_view_request);

        return ['envelope_id' => $envelope_id, 'redirect_url' => $viewUrl['url']];
    }

    /**
     *  Creates envelope definition
     *  Parameters for the envelope: signer_email, signer_name, signer_client_id
     *
     * @param  $args array
     * @return EnvelopeDefinition -- returns an envelope definition
     */
    private static function makeEnvelope(array $args, string $demoPath): EnvelopeDefinition
    {
        # document 1 (pdf) has tag /sn1/
        #
        # The envelope has one recipient.
        # recipient 1 - signer
        #
        # Read the file
        $content_bytes = file_get_contents($demoPath . $GLOBALS['DS_CONFIG']['doc_pdf']);
        $base64_file_content = base64_encode($content_bytes);

        # Create the document model
        $document = new Document(
            [ # create the DocuSign document object
                'document_base64' => $base64_file_content,
                'name' => 'Example document', # can be different from actual file name
                'file_extension' => 'pdf', # many different document types are accepted
                'document_id' => 1 # a label used to reference the doc
            ]
        );

        $phoneNumber = new RecipientIdentityPhoneNumber();
        $phoneNumber->setCountryCode($args['country_code']);
        $phoneNumber->setNumber($args['phone_number']);

        $inputOption = new RecipientIdentityInputOption();
        $inputOption->setName('phone_number_list');
        $inputOption->setValueType('PhoneNumberList');
        $inputOption->setPhoneNumberList(array($phoneNumber));

        $identityVerification = new RecipientIdentityVerification();
        $identityVerification->setWorkflowId($args['workflow_id']);
        $identityVerification->setInputOptions(array($inputOption));


        # Create the signer recipient model
        $signer = new Signer(
            [ # The signer
                'email' => $args['signer_email'],
                'name' => $args['signer_name'],
                'recipient_id' => "1",
                'routing_order' => "1",
                # Setting the client_user_id marks the signer as embedded
                'client_user_id' => $args['signer_client_id'],

            ]
        );

        $signer->setIdentityVerification($identityVerification);




        # Create a sign_here tab (field on the document)
        $sign_here = new SignHere(
            [ # DocuSign SignHere field/tab
                'anchor_string' => '/sn1/',
                'anchor_units' => 'pixels',
                'anchor_y_offset' => '-30',
                'anchor_x_offset' => '20'
            ]
        );

        # Add the tabs model (including the sign_here tab) to the signer
        # The Tabs object wants arrays of the different field/tab types
        $signer->settabs(new Tabs(['sign_here_tabs' => [$sign_here]]));

        # Next, create the top level envelope definition and populate it.
        $envelope_definition = new EnvelopeDefinition(
            [
                'email_subject' => "Please sign this document sent from the PHP SDK",
                'documents' => [$document],
                # The Recipients object wants arrays for each recipient type
                'recipients' => new Recipients(['signers' => [$signer]]),
                'status' => "sent" # requests that the envelope be created and sent.
            ]
        );

        return $envelope_definition;
    }
    # ***DS.snippet.0.end
}
