# PHP Launcher Code Examples

### Github repo: code-examples-php

## Introduction
This repo is a PHP 7.2 application that demonstrates how to:

* **Authenticate with DocuSign** via the
[Authorization Code Grant flow](https://developers.docusign.com/esign-rest-api/guides/authentication/oauth2-code-grant).
  When the token expires, the user is asked to re-authenticate.
  The **refresh token** is not used in this example.

  The [PHP OAuth 2.0 Client package](http://oauth2-client.thephpleague.com/) is used
  for authentication. This example includes a DocuSign OAuth2
  [provider](src/Controllers/Auth/DocuSign.php)
  for the OAuth package, and a [resource owner](src/Controllers/Auth/DocuSignResourceOwner.php) used to process the OAuth::getUser results.

  The OAuth library is used by the example in file
  [index.php](./public/index.php).

1. **Use embedded signing.**
   [Source.](./src/EG001EmbeddedSigning.php)
   This example sends an envelope, and then uses embedded signing for the first signer. With embedded signing, DocuSign signing is initiated from your website.
1. **Send an envelope with a remote (email) signer and cc recipient.**
   [Source.](./src/Controllers/Examples/eSignature/EG002SigningViaEmail.php)
   The envelope includes a PDF, Word, and HTML document. Anchor text ([AutoPlace](https://support.docusign.com/en/guides/AutoPlace-New-DocuSign-Experience)) is used to position the signing fields in the documents.
1. **List envelopes in the user's account.**
   [Source.](./src/Controllers/Examples/eSignature/EG003ListEnvelopes.php)
   The envelopes' current status is included.
1. **Get an envelope's basic information.**
   [Source.](./src/Controllers/Examples/eSignature/EG004EnvelopeInfo.php)
   This example lists the basic information about an envelope, including its overall status.
1. **List an envelope's recipients.**
   [Source.](./src/Controllers/Examples/eSignature/EG005EnvelopeRecipients.php)
   This includes current recipient status.
1. **List an envelope's documents.**
   [Source.](./src/Controllers/Examples/eSignature/EG006EnvelopeDocs.php)
1. **Download an envelope's documents.**
   [Source.](./src/Controllers/Examples/eSignature/EG007EnvelopeGetDoc.php)
   This example can download individual documents, the documents concatenated together, or a ZIP file of the documents.
1. **Programmatically create a template.**
   [Source.](./src/Controllers/Examples/eSignature/EG008CreateTemplate.php)
1. **Send an envelope using a template.**
   [Source.](./src/Controllers/Examples/eSignature/EG009UseTemplate.php)
1. **Send an envelope and upload its documents with multipart binary transfer.**
   [Source.](./src/Controllers/Examples/eSignature/EG010SendBinaryDocs.php)
   Binary transfer is 33% more efficient than using Base64 encoding.
1. **Use embedded sending.**
   [Source.](./src/Controllers/Examples/eSignature/EG011EmbeddedSending.php)
   Embed the DocuSign UI in your web app to finalize or update the envelope and documents before they are sent.
1. **Embed the DocuSign UI in your app.**
   [Source.](./src/Controllers/Examples/eSignature/EG012EmbeddedConsole.php)
1. **Use embedded signing from a template with an added document.**
   [Source.](./src/Controllers/Examples/eSignature/EG013AddDocToTemplate.php) 
   This example sends an envelope based on a template. In addition to the template's document(s), the example adds an  additional document to the envelope by using the [Composite Templates](https://developers.docusign.com/esign-rest-api/guides/features/templates#composite-templates)feature.
1. **Accept payments.** 
   [Source.](./src/Controllers/Examples/eSignature/EG014CollectPayment.php) 
   Send an order form with online payment by credit card.
1. **Get envelope tab data.** 
   [Source.](./src/Controllers/Examples/eSignature/EG015EnvelopeTabData.php) 
   This example retrieves the tab (field) values for all of the envelope's recipients.
1. **Set envelope tab values.** 
   [Source.](./src/Controllers/Examples/eSignature/EG016SetTabValues.php) 
   This example creates an envelope and sets the initial values for its tabs (fields). Some of the tabs are set to be read-only, others can be updated by the recipient. The example also stores metadata with the envelope.
1. **Set template tab values.** 
   [Source.](./src/Controllers/Examples/eSignature/EG017SetTemplateTabValues.php) 
   The example creates an envelope using a template and sets the initial values for its tabs (fields). The example also stores metadata with the envelope.
1. **Get envelope custom field data (metadata).**
   [Source.](./src/Controllers/Examples/eSignature/EG018EnvelopeCustomFieldData.php) 
   This example retrieves the custom metadata (custom data fields) stored with the envelope.   
1. **Require an access code for a recipient.**
   [Source.](./src/Controllers/Examples/eSignature/EG019AccessCodeAuthentication.php)
   This example sends an envelope that requires an access code for the purpose of multifactor authentication.
1. **Require SMS authentication for a recipient.** 
   [Source.](./src/Controllers/Examples/eSignature/EG020SmsAuthentication.php) 
   This example sends an envelope that requires entering in a six-digit code from a text message for the purpose of multifactor authentication.
1. **Require phone authentication for a recipient.** 
   [Source.](./src/Controllers/Examples/eSignature/EG021PhoneAuthentication.php) 
   This example sends an envelope that requires entering in a voice-based response code for the purpose of multifactor authentication.
1. **Require knowledge-based authentication (KBA) for a recipient.**
   [Source.](./src/Controllers/Examples/eSignature/EG022KbAuthentication.php) 
   This example sends an envelope that requires passing a public records check to validate identity for the purpose of multifactor authentication.
1. **Require ID Verification (IDV) for a recipient.**
   [Source.](./src/Controllers/Examples/eSignature/EG023IDVAuthentication.php) 
   This example sends an envelope that requires the recipient to upload a government-issued ID.    
1. **Create a permission profile.**
   [Source.](./src/Controllers/Examples/eSignature/EG024PermissionCreate.php) 
   This code example demonstrates how to create a permission profile.
1. **Set a permission profile.**
   [Source.](./src/Controllers/Examples/eSignature/EG025PermissionSetUserGroup.php)
   This code example demonstrates how to set a user group's permission profile. You must have already a created permission profile and a group of users.
1. **Update individual permission settings.**
   [Source.](./src/Controllers/Examples/eSignature/EG026PermissionChangeSingleSetting.php)
   This code example demonstrates how to edit individual permission settings on a permission profile.
1. **Delete a permission profile.**
   [Source.](./src/Controllers/Examples/eSignature/EG027PermissionDelete.php)
   This code example demonstrates how to delete a permission profile.
1. **Create a brand.**
   [Source.](./src/Controllers/Examples/eSignature/EG028CreateBrand.php)
   This example creates a brand profile for an account.
1. **Apply a brand to an envelope.**
   [Source](./src/Controllers/Examples/eSignature/EG029ApplyBrandToEnvelope.php)
   This code example demonstrates how to apply a brand you've created to an envelope. First, the code creates the envelope and then applies the brand to it. Anchor text ([AutoPlace](https://support.docusign.com/en/guides/AutoPlace-New-DocuSign-Experience)) is used to position the signing fields in the documents.
1. **Apply a brand to a template.**
   [Source.](./src/Controllers/Examples/eSignature/EG030ApplyBrandToTemplate.php)
   This code example demonstrates how to apply a brand you've created to a template. You must have at least one created template and brand. Anchor text ([AutoPlace](https://support.docusign.com/en/guides/AutoPlace-New-DocuSign-Experience)) is used to position the signing fields in the documents.
1. **Bulk-send envelopes to multiple recipients.**
   [Source.](./src/Controllers/Examples/eSignature/EG031BulkSendEnvelopes.php)
   This code example demonstrates how to send envelopes in bulk to multiple recipients. First, the code creates a bulk-send recipients list, and then creates an envelope. After that, it initiates bulk envelope sending.



## Installation

### Prerequisites

**Note: If you downloaded this code using Quickstart from the DocuSign Developer Center, skip items 1 and 2 below as they're automatically created for you.**

1. A DocuSign Developer account (email and password) on [demo.docusign.net](https://demo.docusign.net).
   **Create a [free account](https://go.docusign.com/sandbox/productshot/?elqCampaignId=16532)**
1. A DocuSign integration key (client ID) that is configured for authentication to use the either the
   [Authorization Code Grant](https://developers.docusign.com/platform/auth/authcode) flow or the [JSON Web Token (JWT) Grant](https://developers.docusign.com/platform/auth/jwt) flow.
1. [PHP](https://www.php.net/downloads.php) version 7.2 or later.
1. [Composer](https://getcomposer.org/download/) set up in your PATH environment variable so you can call **composer** from any folder.
1. A name and email for a signer, and a name and email for a cc recipient.   


#### Authorization Code Grant specifics:
   You will need the **integration key** itself and its **secret.**
   The integration key must include a **redirect URI** of

   `{app_url}/index.php?page=ds_callback`

   where `{app_url}` is the URL you have associated with the `/public` folder.

   For example, if you have created a web server that enables the URL

   `http://localhost:8080/example-public`

   to execute files on the `/public` folder of this example, then you must add a **Redirect URI** to
   your integration ley with the value

   `http://localhost:8080/example-public/index.php?page=ds_callback`

#### JWT (JSON Web Token) specifics:
   You will need the **integration key** itself, an RSA private key, and the user ID (GUID) of the impersonated user.

   The private part of the RSA key pair must be copied over and stored in a private.key file in the top of your repo clone. 

### Installation steps
**Note: If you downloaded this code using Quickstart from the DocuSign Developer Center, perform only steps 2, 3, 5, and 7 below, as the other steps are automatically performed for you.**  

1. Download or clone this repository to your workstation to this folder: **code-examples-php**
1. **cd code-examples-php** or `cd <Quickstart_folder_name>`
1. Install the dependencies listed in the composer.json file:
   Run **composer install**  

1. Create a new file, **ds_config.php,** (in the root folderof the example) by using ds_config_example.php as your template.
     Update the integration key and other settings in the configuration file.

   **Note:** Protect your integration key and secret and/or RSA private key. You
   should ensure that the file ds_config.php will not be stored in your source code
   repository.

1. **Configure your web server** to serve the files in the `/public`
  folder. For a simple web server setup, see [XAMPP/Apache web server instructions](https://github.com/docusign/code-examples-php/blob/master/docs/readme_xampp.md). 

1. Update your integration key's settings to include a redirect URI for
   your installation of the example. See Prerequisites item 2 above for more information.

1. Open a browser to http://localhost:8080/public.



## XAMPP/Apache web server installation and configuration

[XAMPP/Apache](https://www.apachefriends.org/download.html) can be configured to run the PHP launcher.

**Step 1.** On the XAMPP Control Panel, to the left of Apache, select the red "X" to install Apache. 

![Install Apache](./docs/apache_x_box.jpg)

The red "X" should become a green checkmark.

![Install Apache](./docs/apache_installed_box.jpg)

**Step 2.** On the XAMPP Control Panel, to the right of Apache, select the Config button > Apache (httpd.conf). The httpd.conf file should open.

![Apache_config](./docs/config_file.jpg)

**Step 3.** In httpd.conf, change the default "Listen 80" to "Listen 8080".

![httpd_listen](./docs/listen_8080_box.jpg)

**Step 4.** In httpd.conf, change the default "ServerName localhost:80" to "ServerName localhost:8080".

![httpd_localhost](./docs/localhost_8080_box.jpg)

**Step 5.** In httpd.conf, change the default<br />
DocumentRoot "C:/xampp/htdocs"<br />
<Directory "C:/xampp/htdocs"><br />
to<br />
DocumentRoot "C:/xampp/htdocs/<Quickstart_folder_name>"<br />
<Directory "C:/xampp/htdocs/<Quickstart_folder_name>"><br />

In httpd.conf, use Ctrl-S to save your changes.

![httpd_DocumentRoot](./docs/Document_Root_box.jpg)

**Step 5.** On the XAMPP Control Panel, to the right of Apache, select the Start button. 

![Apache_start](./docs/apache_start_box.jpg)

Apache should run.

![Apache_run](./docs/start_box.jpg)

**Step 6.** Open [http://localhost:8080/public](http://localhost:8080/public).



#### Payments code example
To use the payments example, create a 
test payments gateway for your DocuSign developer account.

See the
[PAYMENTS_INSTALLATION.md](https://github.com/docusign/code-examples-php/blob/master/PAYMENTS_INSTALLATION.md)
file for instructions.

Then add the payment gateway account ID to the **app/ds_config.php** file.


## License and additional information

### License
This repository uses the MIT License. See the LICENSE file for more information.

### Pull requests
Pull requests are welcomed. Pull requests will only be considered if their content
uses the MIT License.
