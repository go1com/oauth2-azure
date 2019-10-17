<?php
namespace Go1\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class AzureResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;
    /**
     * Raw response
     *
     * @var array
     */
    protected $response;
    /**
     * Creates new resource owner.
     *
     * @param array  $response
     */
    public function __construct(array $response = array())
    {
        $this->response = $response;
    }
    /**
     * Get resource owner ID
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->getValueByKey($this->response, 'id');
    }

    /**
     * Get mail of resource owner
     * @return string|null
     */
    public function getMail()
    {
        return $this->getValueByKey($this->response, 'mail');
    }
    
    /**
     * Retrieve first name of resource owner
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->getValueByKey($this->response, 'givenName');
    }

    /**
     * Retrieve last name of resource owner
     * @return string|null
     */
    public function getLastName()
    {
        return $this->getValueByKey($this->response, 'surname');
    }

    /**
     * Retrieve user principal name of resource owner
     * @return string|null
     */
    public function getUpn()
    {
        return $this->getValueByKey($this->response, 'userPrincipalName');
    }

    /**
     * Retrieve tenant id
     * @return string|null
     */
    public function getTenantId()
    {
        return $this->getValueByKey($this->response, 'tid');
    }

    /**
     * Returns the raw resource owner response.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
