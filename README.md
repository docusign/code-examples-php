# PHP Launcher Code Examples

### Github repo: https://github.com/docusign/code-examples-php

This GitHub repo includes code examples for both the DocuSign eSignature REST API and the DocuSign Rooms API. 

To use the Rooms API code examples, modify the <code>EXAMPLES_API_TYPE</code> setting at the end of the ds_config.php file. Set <code>'Rooms'</code> to <code>true</code> and <code>'ESignature'</code> to <code>false</code>.

**Note:** To use the Rooms API you must also [create your DocuSign developer account for Rooms](https://developers.docusign.com/docs/rooms-api/rooms101/create-account).

## Introduction
This repo is a PHP 7.2 application that demonstrates how to authenticate with DocuSign via [Authorization Code Grant](https://developers.docusign.com/esign-rest-api/guides/authentication/oauth2-code-grant). When the token expires, the user is asked to reauthenticate. The refresh token is not used.

The [PHP OAuth 2.0 Client package](http://oauth2-client.thephpleague.com/) is used for authentication. This launcher includes a DocuSign OAuth2 [provider](src/Controllers/Auth/DocuSign.php) for the OAuth package and a [resource owner](src/Controllers/Auth/DocuSignResourceOwner.php) to process the results of the call to OAuth::getUser.

The OAuth library is used in the file [index.php](./public/index.php).

## eSignature API

For more information about the scopes used for obtaining authorization to use the eSignature API, see the [Required Scopes section](https://developers.docusign.com/docs/esign-rest-api/esign101/auth).

1. **Use embedded signing.**
   [Source](./src/EG001EmbeddedSigning.php)<br />
   Sends an envelope, then uses embedded signing for the first signer. With embedded signing, DocuSign signing is initiated from your website.
1. **Send an envelope with a remote (email) signer and cc recipient.**
   [Source](./src/Controllers/Examples/eSignature/EG002SigningViaEmail.php)<br />
   The envelope includes a PDF, Word, and HTML document. [Anchor text](https://support.docusign.com/en/guides/AutoPlace-New-DocuSign-Experience) is used to position the signing fields in the documents.
1. **List envelopes in the user's account.**
   [Source](./src/Controllers/Examples/eSignature/EG003ListEnvelopes.php)<br />
   The envelopes' current status is included.
1. **Get an envelope's basic information.**
   [Source](./src/Controllers/Examples/eSignature/EG004EnvelopeInfo.php)<br />
   Lists the basic information about an envelope, including its overall status.
1. **List an envelope's recipients.**
   [Source](./src/Controllers/Examples/eSignature/EG005EnvelopeRecipients.php)<br />
   Includes current recipient status.
1. **List an envelope's documents.**
   [Source](./src/Controllers/Examples/eSignature/EG006EnvelopeDocs.php)<br />
   Includes current recipient status.   
1. **Download an envelope's documents.**
   [Source](./src/Controllers/Examples/eSignature/EG007EnvelopeGetDoc.php)<br />
   Downloads individual documents, the documents concatenated together, or a ZIP file of the documents.
1. **Programmatically create a template.**
   [Source](./src/Controllers/Examples/eSignature/EG008CreateTemplate.php)<br />
1. **Send an envelope using a template.**
   [Source](./src/Controllers/Examples/eSignature/EG009UseTemplate.php)<br />
1. **Send an envelope and upload its documents with multipart binary transfer.**
   [Source](./src/Controllers/Examples/eSignature/EG010SendBinaryDocs.php)<br />
   Binary transfer is 33% more efficient than using Base64 encoding.
1. **Use embedded sending.**
   [Source](./src/Controllers/Examples/eSignature/EG011EmbeddedSending.php)<br />
   Embeds the DocuSign UI in your web app to finalize or update the envelope and documents before they are sent.
1. **Embed the DocuSign UI in your app.**
   [Source](./src/Controllers/Examples/eSignature/EG012EmbeddedConsole.php)<br />
1. **Use embedded signing from a template with an added document.**
   [Source](./src/Controllers/Examples/eSignature/EG013AddDocToTemplate.php)<br />
   Sends an envelope based on a template. In addition to the template's document(s), this example adds an additional document to the envelope by using the [Templates](https://developers.docusign.com/esign-rest-api/guides/features/templates#composite-templates) feature.
1. **Accept payments.** 
   [Source](./src/Controllers/Examples/eSignature/EG014CollectPayment.php)<br />
   Sends an order form with online payment by credit card.
1. **Get envelope tab data.** 
   [Source](./src/Controllers/Examples/eSignature/EG015EnvelopeTabData.php)<br />
   Retrieves the tab (field) values for all of the envelope's recipients.
1. **Set envelope tab values.** 
   [Source](./src/Controllers/Examples/eSignature/EG016SetTabValues.php)<br />
   Creates an envelope and sets the initial values for its tabs (fields). Some of the tabs are set to be read-only, others can be updated by the recipient. This example also stores metadata with the envelope.
1. **Set template tab values.** 
   [Source](./src/Controllers/Examples/eSignature/EG017SetTemplateTabValues.php)<br />
   Creates an envelope using a template and sets the initial values for its tabs (fields). This example also stores metadata with the envelope.
1. **Get envelope custom field data (metadata).**
   [Source](./src/Controllers/Examples/eSignature/EG018EnvelopeCustomFieldData.php)<br />
   Retrieves the custom metadata (custom data fields) stored with the envelope. 
1. **Require an access code for a recipient.**
   [Source](./src/Controllers/Examples/eSignature/EG019AccessCodeAuthentication.php)<br />
   Sends an envelope that requires entering an access code for the purpose of multifactor authentication.
1. **Require SMS authentication for a recipient.** 
   [Source](./src/Controllers/Examples/eSignature/EG020SmsAuthentication.php)<br /> 
   Sends an envelope that requires entering a six-digit code from a text message for the purpose of multifactor authentication.
1. **Require phone authentication for a recipient.** 
   [Source](./src/Controllers/Examples/eSignature/EG021PhoneAuthentication.php)<br />
   Sends an envelope that requires entering a voice-based response code for the purpose of multifactor authentication.
1. **Require knowledge-based authentication (KBA) for a recipient.**
   [Source](./src/Controllers/Examples/eSignature/EG022KbAuthentication.php)<br />
   Sends an envelope that requires passing a public records check to validate identity for the purpose of multifactor authentication.
1. **Require ID Verification (IDV) for a recipient.**
   [Source](./src/Controllers/Examples/eSignature/EG023IDVAuthentication.php)<br />
   Sends an envelope that requires the recipient to upload a government-issued ID for the purpose of multifactor authentication.   
1. **Create a permission profile.**
   [Source](./src/Controllers/Examples/eSignature/EG024PermissionCreate.php)<br />
1. **Set a permission profile.**
   [Source](./src/Controllers/Examples/eSignature/EG025PermissionSetUserGroup.php)<br />
   Demonstrates how to set a user group's permission profile. You must have already a created permission profile and a group of users.
1. **Update individual permission settings.**
   [Source](./src/Controllers/Examples/eSignature/EG026PermissionChangeSingleSetting.php)<br />
   Demonstrates how to edit individual permission settings on a permission profile.
1. **Delete a permission profile.**
   [Source](./src/Controllers/Examples/eSignature/EG027PermissionDelete.php)<br />
1. **Create a brand.**
   [Source](./src/Controllers/Examples/eSignature/EG028CreateBrand.php)<br />
   Creates a brand profile for an account.
1. **Apply a brand to an envelope.**
   [Source](./src/Controllers/Examples/eSignature/EG029ApplyBrandToEnvelope.php)<br />
   Demonstrates how to apply a brand you've created to an envelope. First, this example creates the envelope, then applies the brand to it. [Anchor text](https://support.docusign.com/en/guides/AutoPlace-New-DocuSign-Experience) is used to position the signing fields in the documents.
1. **Apply a brand to a template.**
   [Source](./src/Controllers/Examples/eSignature/EG030ApplyBrandToTemplate.php)<br />
   Demonstrates how to apply a brand you've created to a template. You must have at least one created template and brand. [Anchor text](https://support.docusign.com/en/guides/AutoPlace-New-DocuSign-Experience) is used to position the signing fields in the documents.
1. **Bulk-send envelopes to multiple recipients.**
   [Source](./src/Controllers/Examples/eSignature/EG031BulkSendEnvelopes.php)<br />
   Demonstrates how to send envelopes in bulk to multiple recipients. First, this example creates a bulk-send recipients list, then creates an envelope. After that, it initiates bulk envelope sending.

## Rooms API

For more information about the scopes used for obtaining authorization to use the Rooms API, see the [Required Scopes section](https://developers.docusign.com/docs/rooms-api/rooms101/auth/).

**Note:** To use the Rooms API you must also [create your DocuSign Developer Account for Rooms](https://developers.docusign.com/docs/rooms-api/rooms101/create-account). Examples 4 and 6 require that you have the DocuSign Forms feature enabled in your Rooms for Real Estate account.

1. **Create a room with data.**
   [Source](./src/Controllers/Examples/Rooms/EG001CreateRoomWithData.php)<br />
   Creates a new room in your DocuSign Rooms account to be used for a transaction.
1. **Create a room from a template.**
   [Source](./src/Controllers/Examples/Rooms/EG002CreateRoomWithTemplate.php)<br />
   Creates a new room using a template.
1. **Export data from a room.**
   [Source](./src/Controllers/Examples/Rooms/EG003ExportDataFromRoom.php)<br />
   Exports all the available data from a specific room in your DocuSign Rooms account.
1. **Add a form to a room.**
   [Source](./src/Controllers/Examples/Rooms/EG004AddFormsToRoom.php)<br />
   Adds a standard real estate-related form to a specific room in your DocuSign Rooms account.
1. **Search for a room with a filter.**
   [Source](./src/Controllers/Examples/Rooms/EG005GetRoomsWithFilters.php)<br />
   Searches for a room in your DocuSign Rooms account using a specific filter.
1. **Create an external form fillable session.**
   [Source](./src/Controllers/Examples/Rooms/EG006CreateExternalFormFillSession.php)<br />
   Creates an external form that can be filled using DocuSign for a specific room in your DocuSign Rooms account.

## Installation

### Prerequisites

**Note: If you downloaded this code using [Quickstart](https://developers.docusign.com/docs/esign-rest-api/quickstart) from the DocuSign Developer Center, skip items 1 and 2 below as they're automatically performed for you.**

1. [Create a DocuSign developer account](https://go.docusign.com/o/sandbox/) if you don't already have one.

1. A DocuSign integration key (client ID) that is configured for authentication to use either [Authorization Code Grant](https://developers.docusign.com/platform/auth/authcode/) or [JWT Grant](https://developers.docusign.com/platform/auth/jwt/).

   To use [Authorization Code Grant](https://developers.docusign.com/platform/auth/authcode/), you will need an integration key and its secret key. 

   To use [JWT Grant](https://developers.docusign.com/platform/auth/jwt/), you will need an integration key, an RSA key pair, and the **API Username** GUID of the impersonated user. Also, the private key of the RSA key pair must be saved in a new file private.key in the root folder.

1. [PHP](https://www.php.net/downloads.php) version 7.2 or later.

1. [Composer](https://getcomposer.org/download/) set up in your PATH environment variable so you can invoke it from any folder.

1. A name and email for a signer, and a name and email for a cc recipient.   


### Installation steps
**Note: If you downloaded this code using [Quickstart](https://developers.docusign.com/docs/esign-rest-api/quickstart) from the DocuSign Developer Center, skip steps 1, 4, and 6 below as they're automatically performed for you.**  

1. Download or clone the [code-examples-php](https://github.com/docusign/code-examples-php) repository.
1. Switch to the folder: `cd <Quickstart_folder_name>` or `cd code-examples-php`
1. Run `composer install` to install the dependencies listed in the composer.json file.
1. Create a new file ds_config.php in the root folder by using ds_config_example.php as your template. Update the integration key and other settings in ds_config.php.

   **Note:** Protect your integration key and secret and/or RSA private key pair; ensure that ds_config.php will not be stored in your source code repository.

1. Configure your web server to serve the files in the /public folder. For a simple web server setup, see the [XAMPP Apache web server instructions](#xampp-apache-web-server-instructions) below. 
1. Update your integration key's settings to include a redirect URI.

   The integration key must include a redirect URI of 
   
   http://{app_url}/index.php?page=ds_callback

   where {app_url} is the URL you have associated with the /public folder.
   
   For example, if you created a web server that enables the URL 
   
   http://localhost:8080/example-public

   to execute files on the /public folder of this launcher, then you must add a redirect URI of 
   
   http://localhost:8080/example-public/index.php?page=ds_callback

1. Open a browser to http://localhost:8080/public.


## XAMPP Apache web server instructions

[XAMPP Apache web server](https://www.apachefriends.org/download.html) can be configured to run the PHP launcher.

1. Unzip the PHP [Quickstart](https://developers.docusign.com/docs/esign-rest-api/quickstart/) file or download or clone the [code-examples-php](https://github.com/docusign/code-examples-php) repository into your C:/xampp/htdocs folder.

1. Switch to the folder: `cd C:xampp/htdocs/<Quickstart_folder_name>` or `cd C:xampp/htdocs/code-examples-php`

1. Run `composer install` to install the dependencies listed in the composer.json file.

1. Run XAMPP as administrator. On the XAMPP Control Panel, to the left of Apache, select the red "X" to install Apache web server. 

![Install Apache](./docs/apache_x_box.jpg)

The red "X" becomes a green checkmark. 

![Install Apache](./docs/apache_installed_box.jpg)

5. On the XAMPP Control Panel, to the right of Apache, select the Config button > Apache (httpd.conf). The httpd.conf file should open. 

![Apache_config](./docs/config_file.jpg)

6. In the httpd.conf file, change the default `Listen 80` to `Listen 8080`. 

![httpd_listen](./docs/listen_8080_box.jpg)

7. In the httpd.conf file, change the default `ServerName localhost:80` to `ServerName localhost:8080`. 

![httpd_localhost](./docs/localhost_8080_box.jpg)

8. In the httpd.conf file, change the default<br />
`DocumentRoot "C:/xampp/htdocs"`<br />
`<Directory "C:/xampp/htdocs">`<br />
to<br />
`DocumentRoot "C:/xampp/htdocs/<Quickstart_folder_name>"`<br />
`<Directory "C:/xampp/htdocs/<Quickstart_folder_name>"`><br />

In the httpd.conf file, use Ctrl-S to save your changes. 

![httpd_DocumentRoot](./docs/Document_Root_box.jpg)

9. On the XAMPP Control Panel, to the right of Apache, select the Start button. 

![Apache_start](./docs/apache_start_box.jpg)

Apache will run. 

![Apache_run](./docs/start_box.jpg)

10. Open a browser to http://localhost:8080/public.



## Payments code example
To use the payments example, create a test payments gateway for your DocuSign developer account. See [PAYMENTS_INSTALLATION.md](https://github.com/docusign/code-examples-php/blob/master/PAYMENTS_INSTALLATION.md) for instructions.

Then add the **Gateway Account ID** to the ds_config.php file.



## License and additional information

### License
This repository uses the MIT License. See the LICENSE file for more information.

### Pull requests
Pull requests are welcomed. Pull requests will only be considered if their content
uses the MIT License.
