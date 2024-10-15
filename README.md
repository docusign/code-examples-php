# PHP Launcher Code Examples

>
>### PLEASE! Share your feedback in a [two-question survey](https://docs.google.com/forms/d/e/1FAIpQLScPa74hwhJwi7XWDDj4-XZVOQTF9jJWgbIFEpulXokCqYWT4A/viewform?usp=pp_url&entry.680551577=PHP).
>
>
### GitHub repo: [code-examples-php](./README.md)

This GitHub repo includes code examples for the [Web Forms API](https://developers.docusign.com/docs/web-forms-api/), [Maestro API](https://developers.docusign.com/docs/maestro-api/), [Docusign Admin API](https://developers.docusign.com/docs/admin-api/), [Click API](https://developers.docusign.com/docs/click-api/), [eSignature REST API](https://developers.docusign.com/docs/esign-rest-api/), [Monitor API](https://developers.docusign.com/docs/monitor-api/), and [Rooms API](https://developers.docusign.com/docs/rooms-api/). 


## Introduction
This repo is a PHP application that supports the following authentication workflows: 

* Authentication with Docusign via [Authorization Code Grant](https://developers.docusign.com/platform/auth/authcode).
When the token expires, the user is asked to re-authenticate. The refresh token is not used.

* Authentication with Docusign via [JSON Web Token (JWT) Grant](https://developers.docusign.com/platform/auth/jwt/).
When the token expires, it updates automatically.


The [PHP OAuth 2.0 Client package](http://oauth2-client.thephpleague.com/) is used for authentication. This launcher includes a Docusign OAuth2 [provider](src/Controllers/Auth/Docusign.php) for the OAuth package and a [resource owner](src/Controllers/Auth/DocusignResourceOwner.php) to process the results of the call to OAuth::getUser.

The OAuth library is used in the file [index.php](./public/index.php).

## eSignature API

For more information about the scopes used for obtaining authorization to use the eSignature API, see [Required scopes](https://developers.docusign.com/docs/esign-rest-api/esign101/auth#required-scopes).  

For a list of code examples that use the eSignature API, see the [How-to guides overview](https://developers.docusign.com/docs/esign-rest-api/how-to/) on the Docusign Developer Center.


## Rooms API 

**Note:** To use the Rooms API, you must also [create your Rooms developer account](https://developers.docusign.com/docs/rooms-api/rooms101/create-account). Examples 4 and 6 require that you have the Docusign Forms feature enabled in your Rooms for Real Estate account.  
For more information about the scopes used for obtaining authorization to use the Rooms API, see [Required scopes](https://developers.docusign.com/docs/rooms-api/rooms101/auth/).  

For a list of code examples that use the Rooms API, see the [How-to guides overview](https://developers.docusign.com/docs/rooms-api/how-to/) on the Docusign Developer Center.


## Click API  
For more information about the scopes used for obtaining authorization to use the Click API, see [Required scopes](https://developers.docusign.com/docs/click-api/click101/auth/#required-scopes)  

For a list of code examples that use the Click API, see the [How-to guides overview](https://developers.docusign.com/docs/click-api/how-to/) on the Docusign Developer Center.


## Monitor API

**Note:** To use the Monitor API, you must also [enable Docusign Monitor for your organization](https://developers.docusign.com/docs/monitor-api/how-to/enable-monitor/).  

For information about the scopes used for obtaining authorization to use the Monitor API, see the [scopes section](https://developers.docusign.com/docs/monitor-api/monitor101/auth/). 

For a list of code examples that use the Monitor API, see the [How-to guides overview](https://developers.docusign.com/docs/monitor-api/how-to/) on the Docusign Developer Center.


## Admin API

**Note:** To use the Admin API, you must [create an organization](https://support.docusign.com/en/guides/org-admin-guide-create-org) in your Docusign developer account. Also, to run the Docusign CLM code example, [CLM must be enabled for your organization](https://support.docusign.com/en/articles/Docusign-and-SpringCM).

For information about the scopes used for obtaining authorization to use the Admin API, see the [scopes section](https://developers.docusign.com/docs/admin-api/admin101/auth/).

For a list of code examples that use the Admin API, see the [How-to guides overview](https://developers.docusign.com/docs/admin-api/how-to/) on the Docusign Developer Center.


## Web Forms API

The Web Forms API is available in all developer accounts, but only in certain production account plans. Contact [Docusign Support](https://support.docusign.com/) or your account manager to find out whether the Web Forms API is available for your production account plan.

For more information about the scopes used for obtaining authorization to use the Rooms API, see [Required scopes](https://developers.docusign.com/docs/web-forms-api/plan-integration/authentication/).

For a list of code examples that use the Web Forms API, see the [How-to guides overview](https://developers.docusign.com/docs/web-forms-api/how-to/) on the Docusign Developer Center.


## Installation

### Prerequisites

**Note:** If you downloaded this code using [Quickstart](https://developers.docusign.com/docs/esign-rest-api/quickstart) from the Docusign Developer Center, skip items 1 and 2 below as they were automatically performed for you.

1. A free [Docusign developer account](https://www.docusign.com/developers/sandbox); create one if you don't already have one.
1. A Docusign app and integration key that is configured to use either [Authorization Code Grant](https://developers.docusign.com/platform/auth/authcode/) or [JWT Grant](https://developers.docusign.com/platform/auth/jwt/) authentication.

   This [video](https://www.youtube.com/watch?v=eiRI4fe5HgM) demonstrates how to obtain an integration key.  
   
   To use [Authorization Code Grant](https://developers.docusign.com/platform/auth/authcode/), you will need an integration key and a secret key. See [Installation steps](#installation-steps) for details.  

   To use [JWT Grant](https://developers.docusign.com/platform/auth/jwt/), you will need an integration key, an RSA key pair, and the User ID GUID of the impersonated user. See [Installation steps for JWT Grant authentication](#installation-steps-for-jwt-grant-authentication) for details.  

   For both authentication flows:  
   
   If you use this launcher on your own workstation, the integration key must include redirect a URI of http://localhost:8080/public/index.php?page=ds_callback

   If you host this launcher on a remote web server, set your redirect URI as   
   
   {base_url}/index.php?page=ds_callback
   
   where {base_url} is the URL for the web app.


1. [PHP](https://www.php.net/downloads.php) 8.0.0 or later.

1. [Composer](https://getcomposer.org/download/) set up in your PATH environment variable so you can invoke it from any folder.




### Installation steps

**Note:** If you downloaded this code using [Quickstart](https://developers.docusign.com/docs/esign-rest-api/quickstart) from the Docusign Developer Center, skip step 4 below as it was automatically performed for you.  

1. Extract the Quickstart ZIP file or download or clone the code-examples-php repository.
1. In your command-line environment, switch to the folder: `cd <Quickstart_folder>` or `cd code-examples-php`
1. To install dependencies, run: `composer install`
1. To configure the launcher for [Authorization Code Grant](https://developers.docusign.com/platform/auth/authcode/) authentication, create a copy of the file ds_config_example.php and save the copy as ds_config.php.
   1. Add your integration key. On the [Apps and Keys](https://admindemo.docusign.com/authenticate?goTo=apiIntegratorKey) page, under **Apps and Integration Keys**, choose the app to use, then select **Actions > Edit**. Under **General Info**, copy the **Integration Key** GUID and save it in ds_config.php as your `ds_client_id`.
   1. Generate a secret key, if you don’t already have one. Under **Authentication**, select **+ ADD SECRET KEY**. Copy the secret key and save it in ds_config.php as your `ds_client_secret`.
   1. Add the launcher’s redirect URI. Under **Additional settings**, select **+ ADD URI**, and set a redirect URI of http://localhost:8080/public/index.php?page=ds_callback. Select **SAVE**.   
   1. Set a name and email address for the signer. In ds_config.php, save an email address as `signer_email` and a name as `signer_name`.  
**Note:** Protect your personal information. Please make sure that ds_config.php will not be stored in your source code repository. 
1. Configure your web server to serve the files in the /public folder. For a simple web server setup, see the [PHP web server instructions](#php-web-server-instructions) below. 
1. Open a browser to http://localhost:8080/public.


## PHP web server instructions

PHP can be used with the command line to launch a built in web server.

1. Extract the [Quickstart](https://developers.docusign.com/docs/esign-rest-api/quickstart/) ZIP file or download or clone the [code-examples-php](https://github.com/docusign/code-examples-php) repository.  
1. In your command-line environment, switch to the folder: `cd <Quickstart_folder>` or `cd code-examples-php`
1. To install dependencies, run: `composer install`
1. To start a built-in PHP web server, run: `php -S localhost:8080`
1. Open a browser to http://localhost:8080/public.


## XAMPP Apache web server instructions

[XAMPP Apache web server](https://www.apachefriends.org/download.html) can be configured to run the PHP launcher.

1. Extract the Quickstart ZIP file or download or clone the code-examples-php repository into your `C:/xampp/htdocs` folder.

1. In your command-line environment, switch to the folder: `cd C:xampp/htdocs/<Quickstart_folder>` or `cd C:xampp/htdocs/code-examples-php`

1. To install dependencies, run: `composer install`

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
`DocumentRoot "C:/xampp/htdocs/<Quickstart_folder>"`<br />
`<Directory "C:/xampp/htdocs/<Quickstart_folder>"`><br />

In the httpd.conf file, use Ctrl-S to save your changes. 

![httpd_DocumentRoot](./docs/Document_Root_box.jpg)

9. On the XAMPP Control Panel, to the right of Apache, select the Start button. 

![Apache_start](./docs/apache_start_box.jpg)

Apache will run. 

![Apache_run](./docs/start_box.jpg)

10. Open a browser to http://localhost:8080/public.

## Docker instructions

[Docker](https://www.docker.com/get-started) can be configured to run the PHP launcher.

1. Start Docker as administrator. 
1. Extract the [Quickstart](https://developers.docusign.com/docs/esign-rest-api/quickstart/) ZIP file or download or clone the [code-examples-php](https://github.com/docusign/code-examples-php) repository.  
1. In your command-line environment, switch to the folder: `cd <Quickstart_folder>` or `cd code-examples-php`
1. To generate the container used by the launcher, run:  `docker compose up -d`  
1. To install dependencies, run: `docker exec -it --user www-data docusign-php-fpm composer install`
1. Open a browser to http://localhost:8080/public

**Note:** To kill all containers, run: `docker rm -f $(docker ps -a -q)`


## JWT grant remote signing and Authorization Code Grant embedded signing projects
See [Docusign Quickstart overview](https://developers.docusign.com/docs/esign-rest-api/quickstart/overview/) on the Docusign Developer Center for more information on how to run the JWT grant remote signing and the Authorization Code Grant embedded signing project.


## Payments code example  

To use the payments code example, create a test payment gateway on the [Payments](https://admindemo.docusign.com/authenticate?goTo=payments) page in your developer account. See [Configure a payment gateway](./PAYMENTS_INSTALLATION.md) for details.

Once you've created a payment gateway, save the **Gateway Account ID** GUID to ds_config.php.


## License and additional information  

### License  
This repository uses the MIT License. See [LICENSE](./LICENSE) for details.

### Pull Requests
Pull requests are welcomed. Pull requests will only be considered if their content
uses the MIT License.
