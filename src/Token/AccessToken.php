<?php
namespace Go1\OAuth2\Client\Token;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Go1\OAuth2\Client\Provider\Azure;
use League\OAuth2\Client\Token\AccessToken as LeagueAccessToken;
use RuntimeException;

/**
 * https://github.com/TheNetworg/oauth2-azure/blob/master/src/Token/AccessToken.php
 */
class AccessToken extends LeagueAccessToken
{
    protected $idToken;
    protected $idTokenClaims;

    public function __construct(array $options, Azure $provider)
    {
        parent::__construct($options);

        if (!empty($options['id_token'])) {
            $this->idToken = $options['id_token'];
            $keys          = $provider->getJwtVerificationKeys();
            $idTokenClaims = null;
            try {
                $tks = explode('.', $this->idToken);

                $keyArray = [];
                foreach ($keys as $kid => $key) {
                    $keyArray[$kid] = new Key($key, 'RS256');
                }

                // Check if the id_token contains signature
                if (3 == count($tks) && !empty($tks[2])) {
                    $idTokenClaims = (array) JWT::decode($this->idToken, $keyArray);
                } else {
                    // The id_token is unsigned (coming from v1.0 endpoint) - https://msdn.microsoft.com/en-us/library/azure/dn645542.aspx
                    // Since idToken is not signed, we just do OAuth2 flow without validating the id_token
                    // // Validate the access_token signature first by parsing it as JWT into claims
                    // $accessTokenClaims = (array)JWT::decode($options['access_token'], $keys, ['RS256']);
                    // Then parse the idToken claims only without validating the signature
                    $idTokenClaims = (array) JWT::jsonDecode(JWT::urlsafeB64Decode($tks[1]));
                }
            } catch (JWT_Exception $e) {
                throw new RuntimeException('Unable to parse the id_token!');
            }
            if ($provider->getClientId() != $idTokenClaims['aud']) {
                throw new RuntimeException('The audience is invalid!');
            }
            if ($idTokenClaims['nbf'] > time() || $idTokenClaims['exp'] < time()) {
                // Additional validation is being performed in firebase/JWT itself
                throw new RuntimeException('The id_token is invalid!');
            }
            if ('common' === $provider->tenant) {
                $provider->tenant = $idTokenClaims['tid'];
                $tenant = $provider->getOpenIdConfiguration($provider->tenant, $provider->b2cPolicy);
                if ($idTokenClaims['iss'] !== $tenant['issuer']) {
                    throw new RuntimeException('Invalid token issuer!');
                }
            } else {
                $tenant = $provider->getOpenIdConfiguration($provider->tenant, $provider->b2cPolicy);
                if ($idTokenClaims['iss'] !== $tenant['issuer']) {
                    throw new RuntimeException('Invalid token issuer!');
                }
            }
            $this->idTokenClaims = $idTokenClaims;
        }
    }
    public function getIdTokenClaims()
    {
        return $this->idTokenClaims;
    }
}
