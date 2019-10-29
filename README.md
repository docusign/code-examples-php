# PHP: Authorization Code Grant Examples

### Github repo: eg-03-php-auth-code-grant
## Introduction
This repo is a PHP 7.2 application that demonstrates:

* **Authentication with DocuSign** via the
[Authorization Code Grant flow](https://developers.docusign.com/esign-rest-api/guides/authentication/oauth2-code-grant).
  When the token expires, the user is asked to re-authenticate.
  The **refresh token** is not used in this example.

  The [PHP OAuth 2.0 Client package](http://oauth2-client.thephpleague.com/) is used
  for authentication. This example includes a DocuSign OAuth2
  [provider](./src/DocuSign.php)
  for the OAuth package, and a [resource owner](./src/DocuSignResourceOwner.php) used to process the OAuth::getUser results.

  The OAuth library is used by the example in file
  [index.php](./public/index.php).

1. **Embedded Signing Ceremony.**
   [Source.](./src/EG001EmbeddedSigning.php)
   This example sends an envelope, and then uses an embedded signing ceremony for the first signer.
   With embedded signing, the DocuSign signing ceremony is initiated from your website.
1. **Send an envelope with a remote (email) signer and cc recipient.**
   [Source.](./src/EG002SigningViaEmail.php)
   The envelope includes a pdf, Word, and HTML document.
   Anchor text ([AutoPlace](https://support.docusign.com/en/guides/AutoPlace-New-DocuSign-Experience)) is used to position the signing fields in the documents.
1. **List envelopes in the user's account.**
   [Source.](./src/EG003ListEnvelopes.php)
   The envelopes' current status is included.
1. **Get an envelope's basic information.**
   [Source.](./src/EG004EnvelopeInfo.php)
   The example lists the basic information about an envelope, including its overall status.
1. **List an envelope's recipients**
   [Source.](./src/EG005EnvelopeRecipients.php)
   Includes current recipient status.
1. **List an envelope's documents.**
   [Source.](./src/EG006EnvelopeDocs.php)
1. **Download an envelope's documents.**
   [Source.](./src/EG007EnvelopeGetDoc.php)
   The example can download individual
   documents, the documents concatenated together, or a zip file of the documents.
1. **Programmatically create a template.**
   [Source.](./src/EG008CreateTemplate.php)
1. **Send an envelope using a template.**
   [Source.](./src/EG009UseTemplate.php)
1. **Send an envelope and upload its documents with multpart binary transfer.**
   [Source.](./src/EG010SendBinaryDocs.php)
   Binary transfer is 33% more efficient than using Base64 encoding.
1. **Embedded sending.**
   [Source.](./src/EG011EmbeddedSending.php)
   Embeds the DocuSign web tool (NDSE) in your web app to finalize or update
   the envelope and documents before they are sent.
1. **Embedded DocuSign web tool (NDSE).**
   [Source.](./src/EG012EmbeddedConsole.php)
1. **Embedded Signing Ceremony from a template with an added document.**
   [Source.](./src/EG013AddDocToTemplate.php)
   This example sends an envelope based on a template.
   In addition to the template's document(s), the example adds an
   additional document to the envelope by using the
   [Composite Templates](https://developers.docusign.com/esign-rest-api/guides/features/templates#composite-templates)
   feature.
1. Future: Payments example: an order form, with online payment by credit card.

   Note: for PHP, this example is still in production.
   [Source.](./src/EG014CollectPayment.php)
1. **Get the envelope tab data.**
   Retrieve the tab (field) values for all of the envelope's recipients.
   [Source.](./src/EG015EnvelopeTabData.php)
1. **Set envelope tab values.**
   The example creates an envelope and sets the initial values for its tabs (fields). Some of the tabs
   are set to be read-only, others can be updated by the recipient. The example also stores
   metadata with the envelope.
   [Source.](./src/EG016SetTabValues.php)
1. **Set template tab values.**
   The example creates an envelope using a template and sets the initial values for its tabs (fields).
   The example also stores metadata with the envelope.
   [Source.](./src/EG017SetTemplateTabValues.php)
1. **Get the envelope custom field data (metadata).**
   The example retrieves the custom metadata (custom data fields) stored with the envelope.
   [Source.](./src/EG018EnvelopeCustomFieldData.php)
1. **Requiring an Access Code for a Recipient**
   [Source.](./src/EG019AccessCodeAuthentication.php)
   This example sends an envelope that requires an access-code for the purpose of multi-factor authentication.
1. **Requiring SMS authentication for a recipient**
   [Source.](./src/EG020SmsAuthentication.php)
   This example sends an envelope that requires entering in a six digit code from an text message for the purpose of multi-factor authentication.
1. **Requiring Phone authentication for a recipient**
   [Source.](./src/EG021PhoneAuthentication.php)
   This example sends an envelope that requires entering in a voice-based response code for the purpose of multi-factor authentication.
1. **Requiring Knowledge-Based Authentication (KBA) for a Recipient**
   [Source.](./src/EG022KbaAuthentication.php)
   This example sends an envelope that requires passing a Public records check to validate identity for the purpose of multi-factor authentication.
1. **Requiring ID Verification (IDV) for a recipient**
   [Source.](./src/EG023IdvAuthentication.php)
   This example sends an envelope that requires the recipient to upload a government issued id.    



## Installation

### Prerequisites
1. A DocuSign Developer Sandbox account (email and password) on [demo.docusign.net](https://demo.docusign.net).
   Create a [free account](https://go.docusign.com/sandbox/productshot/?elqCampaignId=16532).
1. A DocuSign Integration Key (a client ID) that is configured to use the
   OAuth Authorization Code flow.
   You will need the **Integration Key** itself, and its **secret**.

   The Integration key must include a **Redirect URI** of

   `{app_url}/index.php?page=ds_callback`

   Where `{app_url}` is the url you have associated with the `/public` directory of the example.

   For example, if you have created a web server that enables url

   `http://localhost:8080/example-public`
   to execute files on the `/public` directory of this example, then you must add a **Redirect URI** to
   your Integration Key with value

   `http://localhost:8080/example-public/index.php?page=ds_callback`

1. PHP version 7.2 or later.
1. A name and email for a signer, and a name and email for a cc recipient.

### Installation steps
1. Download or clone this repository to your workstation to directory **eg-03-php-auth-code-grant**
1. **cd eg-03-php-auth-code-grant**
1. Install the dependencies listed in the composer.json file:

   Run **composer install**  
   
   If you don't already have Composer installed: [installation instructions](https://getcomposer.org/doc/00-intro.md)
1. Update the file **ds_config.php** (root level directory of the example)
     with the Integration Key and other settings.

   **Note:** Protect your Integration Key and secret--you
   should ensure that ds_config.php file will not be stored in your source code
   repository.

1. **Configure your web server.** Configure your web server to serve the files in the `/public`
   directory of the example. Configure the web server to automatically open the `index.php`
   file in the directory if a file is not explicitly named in the URL.
   Automatically opening `index.html` files is often a default. You may need to update
   your web server's settings to also use `index.php` automatically. Or you can
   specify the file name manually in the url.
1. Update your Integration Key's settings to include a **Redirect URI** for
   your installation of the example. See Prerequisites item #2, above for more information.
1. Open a browser to the example's base url to view the index page.


#### Payments code example
To use the payments example, create a
test payments gateway for your developer sandbox account.

See the
[PAYMENTS_INSTALLATION.md](https://github.com/docusign/eg-03-python-auth-code-grant/blob/master/PAYMENTS_INSTALLATION.md)
file for instructions.

Then add the payment gateway account id to the **app/ds_config.php** file.

## Using the examples with other authentication flows

The examples in this repository can also be used with either the
Implicit Grant or JWT OAuth flows.
See the [Authentication guide](https://developers.docusign.com/esign-rest-api/guides/authentication)
for information on choosing the right authentication flow for your application.

## License and additional information

### License
This repository uses the MIT License. See the LICENSE file for more information.

### Pull Requests
Pull requests are welcomed. Pull requests will only be considered if their content
uses the MIT License.
