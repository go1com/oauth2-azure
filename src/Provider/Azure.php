<?php
namespace Go1\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;

class Azure extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public $tenant = 'common';
    public $isB2C = false;

    protected $configurationUrlFormat = 'https://login.microsoftonline.com/%s/v2.0/.well-known/openid-configuration';
    protected $b2cConfigurationUrlFormat = 'https://%s.b2clogin.com/%s.onmicrosoft.com/oauth2/.well-known/openid-configuration';
    
    protected $openIdConfigurationUrl = null;
    protected $openIdConfiguration = [];

    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);

        $this->isB2C = false;
        if (isset($options['b2c']) && $options['b2c']) {
            $this->isB2C = true;
        }

        if (isset($options['tenant'])) {
            $this->tenant = $options['tenant'];
        }
    }

    /**
     * Get OAuth2 configuration from Azure
     *
     * @param string $tenant
     * @param boolean $isB2C
     * 
     * @return array
     */
    public function getOpenIdConfiguration(string $tenant, bool $isB2C = false): array
    {
        if (!array_key_exists($tenant, $this->openIdConfiguration)) {
            $this->openIdConfiguration[$tenant] = [];

            $openIdConfigurationUrl = sprintf($this->configurationUrlFormat, $tenant);
            if ($isB2C) {
                $openIdConfigurationUrl = sprintf($this->b2cConfigurationUrlFormat, $tenant, $tenant);
            }

            $factory = $this->getRequestFactory();
            $request = $factory->getRequestWithOptions('get', $openIdConfigurationUrl, []);
            $response = $this->getParsedResponse($request);

            $this->openIdConfiguration[$tenant] = $response;
        }
        
        return $this->openIdConfiguration[$tenant];
    }

    public function getBaseAuthorizationUrl()
    {
        $openIdConfiguration = $this->getOpenIdConfiguration($this->tenant, $this->isB2C);
        return $openIdConfiguration['authorization_endpoint'];
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        $openIdConfiguration = $this->getOpenIdConfiguration($this->tenant, $this->isB2C);
        return $openIdConfiguration['token_endpoint'];
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return null;
    }

    protected function getDefaultScopes()
    {
        return [];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (isset($data['odata.error']) || isset($data['error'])) {
            if (isset($data['odata.error']['message']['value'])) {
                $message = $data['odata.error']['message']['value'];
            } elseif (isset($data['error']['message'])) {
                $message = $data['error']['message'];
            } else {
                $message = $response->getReasonPhrase();
            }
            throw new IdentityProviderException(
                $message,
                $response->getStatusCode(),
                $response
            );
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new GenericResourceOwner($response, $response['oid']);
    }
}
