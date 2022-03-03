<?php

namespace Example\Services\Examples\eSignature;

use DocuSign\eSign\Model\Document;
use DocuSign\eSign\Model\EnvelopeDefinition;

class CreateAnEnvelopeFunctionService
{
    public static function make_envelope(array $args, $clientService, $demoDocsPath): EnvelopeDefinition
    {
        $envelope_definition = new EnvelopeDefinition([
            'email_subject' => 'Please sign this document set'
        ]);
        $doc1_b64 = base64_encode($clientService->createDocumentForEnvelope($args));
        # read files 2 and 3 from a local directory
        # The reads could raise an exception if the file is not available!
        $content_bytes = file_get_contents($demoDocsPath . $GLOBALS['DS_CONFIG']['doc_docx']);
        $doc2_b64 = base64_encode($content_bytes);
        $content_bytes = file_get_contents($demoDocsPath . $GLOBALS['DS_CONFIG']['doc_pdf']);
        $doc3_b64 = base64_encode($content_bytes);

        # Create the document models
        $document1 = new Document([  # create the DocuSign document object
            'document_base64' => $doc1_b64,
            'name' => 'Order acknowledgement',  # can be different from actual file name
            'file_extension' => 'html',  # many different document types are accepted
            'document_id' => '1'  # a label used to reference the doc
        ]);
        $document2 = new Document([  # create the DocuSign document object
            'document_base64' => $doc2_b64,
            'name' => 'Battle Plan',  # can be different from actual file name
            'file_extension' => 'docx',  # many different document types are accepted
            'document_id' => '2'  # a label used to reference the doc
        ]);
        $document3 = new Document([  # create the DocuSign document object
            'document_base64' => $doc3_b64,
            'name' => 'Lorem Ipsum',  # can be different from actual file name
            'file_extension' => 'pdf',  # many different document types are accepted
            'document_id' => '3'  # a label used to reference the doc
        ]);
        # The order in the docs array determines the order in the envelope
        $envelope_definition->setDocuments([$document1, $document2, $document3]);
        return $envelope_definition;
    }
}
