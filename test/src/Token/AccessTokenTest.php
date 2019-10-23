<?php

namespace Go1\OAuth2\Client\Test\Token;

use Go1\OAuth2\Client\Provider\Azure;
use Go1\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use PHPUnit\Framework\TestCase;

class AccessTokenTest extends TestCase {

    public function testItCanBeConstructed()
    {
        $provider = new Azure();
        $token = new AccessToken([
            'access_token' => '123'
        ], $provider);

        $this->assertInstanceOf(AccessTokenInterface::class, $token);
    }
}