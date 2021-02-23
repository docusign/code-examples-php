<?php
/**
 * Created by PhpStorm.
 * User: larry.kluger
 * Date: 11/22/18
 * Time: 9:42 PM
 */

namespace Example\Controllers\Auth;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class DocuSignResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response
     *
     * @var array
     */
    protected $response;

    /**
     * The default or selected account.
     * If targetAccountId option was set then that account will be selected.
     * Else (usual case), the user's default account will be selected.
     * @var array [ <account_id>, <is_default>, <account_name>, <base_url>,
     *      (optional) <organization> info ]
     *
     * Example:
     *      "account_id": "7f09961a-a22e-4ea2-8395-aaaaaaaaaaaa",
     *      "is_default": true,
     *      "account_name": "ACME Supplies",
     *      "base_uri": "https://demo.docusign.net",
     *      "organization": {
     *          "organization_id": "9dd9d6cd-7ad1-461a-a432-aaaaaaaaaaaa",
     *          "links": [
     *              {
     *                  "rel": "self",
     *                  "href": "https://account-d.docusign.com/organizations/9dd9d6cd-7ad1-461a-a432-aaaaaaaaaaaa"
     *              }
     *          ]
     *      }
     */
    protected $account_info = false;

    public $target_account_id = false;

    /**
     * Creates new resource owner.
     *
     * @param array $response
     * @throws \Exception if an account is selected but not found.
     */
    public function __construct(array $response = array())
    {
        $this->response = $response;
        $this->target_account_id = $GLOBALS['DS_CONFIG']['target_account_id'];

        // Find the selected or default account
        if ($this->target_account_id) {
            foreach ($response['accounts'] as $account_info)
            {
                if ($account_info['account_id'] == $this->target_account_id)
                {
                    $this->account_info = $account_info;
                    break;
                }
            }
            if (! $this->account_info) {
                throw new \Exception("Targeted Account Id not found.");
            }
        } else {
            // Find the default account info
            foreach ($response['accounts'] as $account_info)
            {
                if ($account_info['is_default'])
                {
                    $this->account_info = $account_info;
                    break;
                }
            }
        }

    }
    /**
     * Returns the identifier of the authorized resource owner.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->getUserId();
    }

    /**
     * Get resource owner id
     *
     * @return string|null
     */
    public function getUserId()
    {
        return $this->response['sub'] ?: null;
    }

    /**
     * Get resource owner email
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->response['email'] ?: null;
    }

    /**
     * Get resource owner name
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->response['name'] ?: null;
    }

    /**
     * Get selected account info
     *
     * @return [account_id, is_default, account_name, base_url]
     */
    public function getAccountInfo()
    {
        return $this->account_info;
    }

    /**
     * Get array of account info for the user's accounts
     * An account's info may include organization info
     *
     * @return array
     */
    public function getAccounts()
    {
        return $this->response['accounts'];
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
