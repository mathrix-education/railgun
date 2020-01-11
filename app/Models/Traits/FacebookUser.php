<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Facebook\FacebookResponse;
use Jose\Component\Signature\JWS;
use Mathrix\Lumen\JWT\Auth\JWT\JWTIssuer;
use const JSON_THROW_ON_ERROR;
use function app;
use function array_replace_recursive;
use function env;
use function implode;
use function json_decode;

/**
 * Trait FacebookUser.
 *
 * @property-read bool $facebook
 * @mixin User
 */
trait FacebookUser
{
    /**
     * @param string $shortAccessToken
     *
     * @return User
     *
     * @throws FacebookSDKException
     */
    public function setFacebookAccessToken(string $shortAccessToken): User
    {
        /** @var Facebook $facebook */
        $facebook = app()->make(Facebook::class);

        // Get long-lived token
        $params   = [
            'grant_type=fb_exchange_token',
            'client_id=' . env('FACEBOOK_APP_ID'),
            'client_secret=' . env('FACEBOOK_APP_SECRET'),
            "fb_exchange_token=$shortAccessToken",
        ];
        $response = $facebook->get('/oauth/access_token?' . implode('&', $params));
        $body     = json_decode($response->getBody(), false, 512, JSON_THROW_ON_ERROR);

        $metadata                             = $this->metadata ?? [];
        $metadata['facebook']['access_token'] = $body->access_token;
        $metadata['facebook']['expires_in']   = Carbon::now()->timestamp + $body->expires_in;
        $this->metadata                       = $metadata;

        return $this;
    }

    /**
     * @param FacebookResponse|null $response
     *
     * @return User
     *
     * @throws FacebookSDKException
     */
    public function syncFacebookProfile(?FacebookResponse $response = null): User
    {
        if ($response === null) {
            /** @var Facebook $facebook */
            $facebook    = app()->make(Facebook::class);
            $accessToken = $this->metadata['facebook']['access_token'];
            $response    = $facebook->get('/me?fields=id,first_name,last_name,email', $accessToken);
        }

        $body = json_decode($response->getBody(), false, 512, JSON_THROW_ON_ERROR);

        $this->metadata  = array_replace_recursive($this->metadata, ['facebook' => ['id' => $body->id]]);
        $this->email     = $body->email;
        $this->username  = $body->first_name;
        $this->firstname = $body->first_name;
        $this->lastname  = $body->last_name;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function getFacebookJWS(): JWS
    {
        $expiresIn = $this->metadata['facebook']['expires_in'];
        $jwtIssuer = app()->make(JWTIssuer::class);

        return $jwtIssuer->issueJWS($this, ['exp' => $expiresIn]);
    }
}
