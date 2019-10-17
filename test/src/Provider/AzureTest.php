<?php

namespace Go1\OAuth2\Client\Test\Provider;

use Go1\OAuth2\Client\Provider\Azure;
use PHPUnit\Framework\TestCase;

class AzureTest extends TestCase
{
    /**
     * Undocumented variable
     *
     * @var Azure
     */
    protected $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = new Azure([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'mock_redirect_uri'
        ]);
    }
    
    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);
        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
    }
}