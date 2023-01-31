<?php

namespace Example\Tests;

$configFile = __DIR__ . '/../ds_config.php';

if (file_exists($configFile)) {
    include_once $configFile;
}


final class TestConfig
{
    /**
     * $clientId
     *
     * @var string
     */
    protected string $clientId;

    /**
     * $host
     *
     * @var string
     */
    protected string $host;

    /**
     * $accountId
     *
     * @var string
     */
    protected string $accountId;

    /**
     * $signerEmail
     *
     * @var string
     */
    protected string $signerEmail;

    /**
     * $signerName
     *
     * @var string
     */
    protected string $signerName;

    /**
     * $impersonatedUserId
     *
     * @var string
     */
    protected string $impersonatedUserId;

    /**
     * $oauthBasePath
     *
     * @var string
     */
    protected string $oauthBasePath;

    /**
     * $privateKey
     *
     * @var string
     */
    protected string $privateKey;

    /**
     * $accessToken
     *
     * @var string
     */
    protected string $accessToken;

    /**
     * $basePath
     *
     * @var string
     */
    protected string $basePath;

    /**
     * $templateId
     *
     * @var string
     */
    protected string $templateId;

    /**
     * $pathToDocuments
     *
     * @var string
     */
    protected string $pathToDocuments;

    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new TestConfig();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $this->pathToDocuments = __DIR__ . "/";
        $this->host = "https://demo.docusign.net/restapi";
        $this->oauthBasePath = "account-d.docusign.com";
        $this->privateKey = $this->pathToDocuments . "private.key";
        $this->basePath = "https://demo.docusign.net";

        if (file_exists(__DIR__ . '/../ds_config.php')) {
            $this->impersonatedUserId = $GLOBALS['JWT_CONFIG']['ds_impersonated_user_id'];
            $this->clientId = $GLOBALS['JWT_CONFIG']['ds_client_id'];
            $this->signerName = $GLOBALS['DS_CONFIG']['signer_name'];
            $this->signerEmail = $GLOBALS['DS_CONFIG']['signer_email'];
            $this->privateKey = file_get_contents($GLOBALS['JWT_CONFIG']['private_key_file']);
        } else {
            $this->impersonatedUserId = getenv("IMPERSONATED_USER_ID");
            $this->clientId = getenv("CLIENT_ID");
            $this->signerName = getenv("SIGNER_NAME");
            $this->signerEmail = getenv("SIGNER_EMAIL");
            $this->privateKey = getenv("PRIVATE_KEY");
        }
    }

    /**
     * Gets clientId
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Sets clientId
     *
     * @param string $clientId
     * @return $this
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * Gets host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Sets host
     *
     * @param string $host
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Gets accountId
     *
     * @return string
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * Sets accountId
     *
     * @param string $accountId
     * @return $this
     */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;
        return $this;
    }

    /**
     * Gets signerEmail
     *
     * @return string
     */
    public function getSignerEmail()
    {
        return $this->signerEmail;
    }

    /**
     * Sets signerEmail
     *
     * @param string $signerEmail
     * @return $this
     */
    public function setSignerEmail($signerEmail)
    {
        $this->signerEmail = $signerEmail;
        return $this;
    }

    /**
     * Gets signerName
     *
     * @return string
     */
    public function getSignerName()
    {
        return $this->signerName;
    }

    /**
     * Sets signerName
     *
     * @param string $signerName
     * @return $this
     */
    public function setSignerName($signerName)
    {
        $this->signerName = $signerName;
        return $this;
    }

    /**
     * Gets impersonatedUserId
     *
     * @return string
     */
    public function getImpersonatedUserId()
    {
        return $this->impersonatedUserId;
    }

    /**
     * Sets impersonatedUserId
     *
     * @param string $impersonatedUserId
     * @return $this
     */
    public function setImpersonatedUserId($impersonatedUserId)
    {
        $this->impersonatedUserId = $impersonatedUserId;
        return $this;
    }

    /**
     * Gets templateId
     *
     * @return string
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }

    /**
     * Sets templateId
     *
     * @param string $templateId
     * @return $this
     */
    public function setTemplateId($templateId)
    {
        $this->templateId = $templateId;
        return $this;
    }

    /**
     * Gets oauthBasePath
     *
     * @return string
     */
    public function getOAuthBasePath()
    {
        return $this->oauthBasePath;
    }

    /**
     * Sets oauthBasePath
     *
     * @param string $envelopeId
     * @return $this
     */
    public function setOAuthBasePath($oauthBasePath)
    {
        $this->oauthBasePath = $oauthBasePath;
        return $this;
    }

    /**
     * Gets privateKey
     *
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * Sets privateKey
     *
     * @param string $createdEnvelopeId
     * @return $this
     */
    public function setPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;
        return $this;
    }

    /**
     * Gets accessToken
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Sets accessToken
     *
     * @param string $accessToken
     * @return $this
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * Gets basePath
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Sets basePath
     *
     * @param string $basePath
     * @return $this
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
        return $this;
    }

    /**
     * Gets pathToDocuments
     *
     * @return string
     */
    public function getPathToDocuments()
    {
        return $this->pathToDocuments;
    }

    /**
     * Set pathToDocuments
     *
     * @param string $pathToDocuments
     * @return $this
     */
    public function setPathToDocuments($pathToDocuments)
    {
        $this->pathToDocuments = $pathToDocuments;
        return $this;
    }
}
