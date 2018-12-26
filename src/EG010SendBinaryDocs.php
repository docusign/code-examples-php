<?php
/**
 * Example 010: Send binary docs with multipart mime: Remote signer, cc; the envelope has three documents
 */

namespace Example;
class EG010SendBinaryDocs
{
    private $eg = "eg010";  # reference (and url) for this example

    public function controller()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == 'GET') {
            $this->getController();
        };
        if ($method == 'POST') {
            check_csrf();
            $this->createController();
        };
    }

    /**
     * 1. Check the token
     * 2. Call the worker method
     */
    private function createController()
    {
        $minimum_buffer_min = 3;
        if (ds_token_ok($minimum_buffer_min)) {
            # 2. Call the worker method
            # More data validation would be a good idea here
            # Strip anything other than characters listed
            $signer_name  = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_name' ]);
            $signer_email = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['signer_email']);
            $cc_name      = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_name'     ]);
            $cc_email     = preg_replace('/([^\w \-\@\.\,])+/', '', $_POST['cc_email'    ]);
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
                'envelope_args' => $envelope_args
            ];

            try {
                $results = $this->worker($args);

            } catch (\GuzzleHttp\Exception\TransferException $e) {
                if ($e->hasResponse()) {
                    $response = $e->getResponse();
                    $json_response = json_decode((string)$response->getBody());
                    $error_code = $json_response->errorCode;
                    $error_message = $json_response->message;
                } else {
                    $error_code = "API request problem";
                    $error_message = (string)$e;
                }

                $GLOBALS['twig']->display('error.html', [
                        'error_code' => $error_code,
                        'error_message' => $error_message]
                );
                exit();
            }
            if ($results) {
                $_SESSION["envelope_id"] = $results["envelope_id"]; # Save for use by other examples
                                                                    # which need an envelopeId
                $GLOBALS['twig']->display('example_done.html', [
                    'title' => "Envelope sent",
                    'h1' => "Envelope sent",
                    'message' => "The envelope has been created and sent!<br/>
                        Envelope ID {$results["envelope_id"]}."
                ]);
                exit;
            }
        } else {
            flash('Sorry, you need to re-authenticate.');
            # We could store the parameters of the requested operation
            # so it could be restarted automatically.
            # But since it should be rare to have a token issue here,
            # we'll make the user re-enter the form data after
            # authentication.
            $_SESSION['eg'] = $GLOBALS['app_url'] . 'index.php?page=' . $this->eg;
            header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=must_authenticate');
            exit;
        }
    }


    /**
     * Do the work of the example
     * This function does the work of creating the envelope by using
     * the API directly with multipart mime
     * @param $args
     * @return array ['redirect_url']
     * @throws \GuzzleHttp\Exception\RequestException
     * @throws \GuzzleHttp\Exception\TransferException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    # ***DS.snippet.0.start
    private function worker($args)
    {
        $envelope_args = $args["envelope_args"];
        # 1. Create the envelope JSON request object
        $envelope_JSON = $this->make_envelope_JSON($envelope_args);

        # 2. Gather documents and their headers
        # Read files 2 and 3 from a local directory
        # The reads could raise an exception if the file is not available!
        # Note: the files are not binary encoded!
        $demo_docs_path = __DIR__ . '/../public/demo_documents/';
        $doc2_docx_bytes = file_get_contents($demo_docs_path . $GLOBALS['DS_CONFIG']['doc_docx']);
        $doc3_pdf_bytes = file_get_contents($demo_docs_path . $GLOBALS['DS_CONFIG']['doc_pdf']);

        $documents = [
            ['mime' => "text/html", 'filename' => $envelope_JSON['documents'][0]['name'],
             'document_id' => $envelope_JSON['documents'][0]['documentId'],
             'bytes' => $this->create_document1($envelope_args)],
            ['mime' => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
             'filename' => $envelope_JSON['documents'][1]['name'],
             'document_id' => $envelope_JSON['documents'][1]['documentId'],
             'bytes' => $doc2_docx_bytes],
            ['mime' => "application/pdf", 'filename' => $envelope_JSON['documents'][2]['name'],
             'document_id' => $envelope_JSON['documents'][2]['documentId'],
             'bytes' => $doc3_pdf_bytes]
        ];

        # Step 3. Create the multipart body
        $CRLF = "\r\n";
        $boundary = "multipartboundary_multipartboundary";
        $hyphens = "--";

        $req_body = ""
            . $hyphens . $boundary
            . $CRLF . "Content-Type: application/json"
            . $CRLF . "Content-Disposition: form-data"
            . $CRLF
            . $CRLF . json_encode($envelope_JSON, JSON_PRETTY_PRINT);

        # Loop to add the documents.
        # See section Multipart Form Requests on page
        # https://developers.docusign.com/esign-rest-api/guides/requests-and-responses
        foreach ($documents as $d) {
            $content_disposition = "Content-Disposition: file; filename=\"{$d['filename']}\";" .
                "documentid={$d['document_id']}";
            $req_body .= $CRLF . $hyphens . $boundary
                . $CRLF . "Content-Type: {$d['mime']}"
                . $CRLF . $content_disposition
                . $CRLF
                . $CRLF . $d['bytes'];
        }
        # Add closing $boundary
        $req_body .= $CRLF . $hyphens . $boundary . $hyphens . $CRLF;

        # Step 2. call Envelopes::create API method
        # using Guzzle https://guzzle.readthedocs.io/en/latest/index.html
        $client = new \GuzzleHttp\Client();
        $uri = "{$args['base_path']}/v2/accounts/{$args['account_id']}/envelopes";
        $results = $client->request('POST', $uri, [
            'headers' => [
                'Authorization' => "bearer {$args['ds_access_token']}",
                'Accept' => 'application/json',
                'Content-Type' => "multipart/form-data; boundary={$boundary}"
            ],
            'body' => $req_body
        ]);
        $json_results = json_decode((string)$results->getBody());

        return ['envelope_id' => $json_results->envelopeId];
    }

    /**
     * Creates envelope definition JSON as an associative array
     * Document 1: An HTML document.
     * Document 2: A Word .docx document.
     * Document 3: A PDF document.
     * DocuSign will convert all of the documents to the PDF format.
     * The recipients' field tags are placed using <b>anchor</b> strings.
     * @param array args parameters for the envelope:
     * @return mixed {Envelope} An envelope definition
     */
    private function make_envelope_JSON($args)
    {
        # create the envelope definition
        $env_json = [];
        $env_json['emailSubject'] = 'Please sign this document set';

        # add the documents
        $doc1 = [
            'name' => 'Order acknowledgement', # can be different from actual file name
            'fileExtension' => 'html', # Source data format. Signed docs are always pdf.
            'documentId' => '1' ]; # a label used to reference the doc
        $doc2 = [
            'name' => 'Battle Plan', 'fileExtension' => 'docx', 'documentId' => '2'];
        $doc3 = [
            'name' => 'Lorem Ipsum', 'fileExtension' => 'pdf', 'documentId' => '3'];
        # The order in the docs array determines the order in the envelope
        $env_json['documents'] = [$doc1, $doc2, $doc3];

        # create a signer recipient to sign the document, identified by name and email
        $signer1 = [
            'email' => $args['signer_email'], 'name' => $args['signer_name'],
            'recipientId' => '1', 'routingOrder' => '1'];
            # routingOrder (lower means earlier) determines the order of deliveries
            # to the recipients. Parallel routing order is supported by using the
            # same integer as the order for two or more recipients.

        # create a cc recipient to receive a copy of the documents, identified by name and email
        $cc1 = [
            'email' => $args['cc_email'], 'name' => $args['cc_name'],
            'routingOrder' => '2', 'recipientId' => '2'];

        # Create signHere fields (also known as tabs) on the documents,
        # We're using anchor (autoPlace) positioning
        #
        # The DocuSign platform searches throughout your envelope's
        # documents for matching anchor strings. So the
        # signHere2 tab will be used in both document 2 and 3 since they
        # use the same anchor string for their "signer 1" tabs.
        $sign_here1 = [
            'anchorString' => '**signature_1**', 'anchorYOffset' => '10', 'anchorUnits' => 'pixels',
            'anchorXOffset' => '20'];
        $sign_here2 = [
            'anchorString' => '/sn1/', 'anchorYOffset' => '10', 'anchorUnits' => 'pixels',
            'anchorXOffset' => '20'];

        # Tabs are set per recipient / signer
        $signer1_tabs = ['signHereTabs' => [$sign_here1, $sign_here2]];
        $signer1["tabs"] = $signer1_tabs;

        # Add the recipients to the envelope object
        $recipients = ['signers' => [$signer1], 'carbonCopies' => [$cc1]];
        $env_json['recipients'] = $recipients;

        # Request that the envelope be sent by setting |status| to "sent".
        # To request that the envelope be created as a draft, set to "created"
        $env_json['status'] = 'sent';

    return $env_json;

    }


    /**
     * Creates a customized html document for the envelope
     * @param array args $
     * @return string -- the html document
     */
    private function create_document1($args)
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
    # ***DS.snippet.0.end


    /**
     * Show the example's form page
     */
    private function getController()
    {
        if (ds_token_ok()) {
            $template_id = isset($_SESSION['template_id']) ? $_SESSION['template_id'] : false;
            $basename = basename(__FILE__);
            $GLOBALS['twig']->display('eg010_send_binary_docs.html', [
                'title' => "Send binary documents",
                'source_file' => $basename,
                'source_url' => $GLOBALS['DS_CONFIG']['github_example_url'] . $basename,
                'documentation' => $GLOBALS['DS_CONFIG']['documentation'] . $this->eg,
                'show_doc' => $GLOBALS['DS_CONFIG']['documentation'],
                'signer_name' => $GLOBALS['DS_CONFIG']['signer_name'],
                'signer_email' => $GLOBALS['DS_CONFIG']['signer_email']
            ]);
        } else {
            # Save the current operation so it will be resumed after authentication
            $_SESSION['eg'] = $GLOBALS['app_url'] . 'index.php?page=' . $this->eg;
            header('Location: ' . $GLOBALS['app_url'] . 'index.php?page=must_authenticate');
            exit;
        }
    }
}

