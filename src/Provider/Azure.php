<?php
namespace Go1\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Azure extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public $tenant = 'common';
    public $b2cPolicy = null;

    protected $configurationUrlFormat = 'https://login.microsoftonline.com/%s/v2.0/.well-known/openid-configuration';
    protected $b2cConfigurationUrlFormat = 'https://%s.b2clogin.com/%s.onmicrosoft.com/v2.0/.well-known/openid-configuration?p=%s';
    
    protected $openIdConfigurationUrl = null;
    protected $openIdConfiguration = [];

    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);

        if (isset($options['policy']) && $options['policy']) {
            $this->b2cPolicy = $options['policy'];
        }

        if (isset($options['tenant'])) {
            $this->tenant = $options['tenant'];
        }
    }
    
    /**
     * Get OAuth2 configuration from Azure
     *
     * @param string $tenant
     * @param string $b2cPolicy
     * 
     * @return array
     * @throws IdentityProviderException
     */
    public function getOpenIdConfiguration(string $tenant, string $b2cPolicy = null): array
    {
        if (!array_key_exists($tenant, $this->openIdConfiguration)) {
            $this->openIdConfiguration[$tenant] = [];

            $openIdConfigurationUrl = sprintf($this->configurationUrlFormat, $tenant);
            if ($b2cPolicy) {
                $openIdConfigurationUrl = sprintf($this->b2cConfigurationUrlFormat, $tenant, $tenant, $b2cPolicy);
            }

            $factory = $this->getRequestFactory();
            $request = $factory->getRequestWithOptions('get', $openIdConfigurationUrl, []);
            $response = $this->getParsedResponse($request);

            if (false === is_array($response)) {
                throw new IdentityProviderException('Invalid OpenID Configuration', 0, $response);
            }

            $this->openIdConfiguration[$tenant] = $response;
        }

        return $this->openIdConfiguration[$tenant];
    }

    public function getBaseAuthorizationUrl()
    {
        $openIdConfiguration = $this->getOpenIdConfiguration($this->tenant, $this->b2cPolicy);
        return $openIdConfiguration['authorization_endpoint'];
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        $openIdConfiguration = $this->getOpenIdConfiguration($this->tenant, $this->b2cPolicy);
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
