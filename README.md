# Azure Active Directory Provider for OAuth 2.0 Client

[![Build Status](https://img.shields.io/github/build/go1com/oauth2-azure)](https://travis-ci.org/go1com/oauth2-azure.svg?branch=master)
[![Software License](https://img.shields.io/github/license/go1com/oauth2-azure)](https://github.com/go1com/oauth2-azure/blob/master/LICENSE)

This package provide Microsoft Azure AD OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

This packages focuses on Microsoft Azure AD [v2.0 APIs](https://docs.microsoft.com/en-us/azure/active-directory/develop/v2-protocols-oidc).

## Installation

To install, use composer:

```
composer require go1/oauth2-azure
```

## Usage

### Authorization Code Flow

```php
$provider = new Go1\OAuth2\Client\Provider\Azure([
    'clientId'          => '{azure-client-id}',
    'clientSecret'      => '{azure-client-secret}',
    'redirectUri'       => 'https://example.com/callback-url',
]);

if (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code'],
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the user's details
        $user = $provider->getResourceOwner($token);

        // Use these details to create a new profile
        printf('Hello %s!', $user->getName());

    } catch (Exception $e) {

        // Failed to get user details
        exit('Oh dear...');
    }

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}
```


## B2C

```php
$provider = new Go1\OAuth2\Client\Provider\Azure([
    'clientId'      => '{azure-client-id}',
    'clientSecret'  => '{azure-client-secret}',
    'redirectUri'   => 'https://example.com/callback-url',
    'tenant'        => 'myazuretenant',
    'policy'        => 'b2c_1_sign_in'
]);
```


## Shout Outs

Thanks to [thenetworg/oauth2-azure](https://github.com/TheNetworg/oauth2-azure) for the inspiration.

## License
The MIT License (MIT). Please see [License File](https://github.com/go1/oauth2-azure/blob/master/LICENSE) for more information.