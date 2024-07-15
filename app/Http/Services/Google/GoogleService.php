<?php

namespace App\Http\Services\Google;

use Google_Client;
use Google_Service_Oauth2;

class GoogleService
{
    public function __construct(
        public Google_Client $client
    )
    {
        $this->client->setClientId(env('GOOGLE_CLIENT_ID'));
        $this->client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $this->client->setRedirectUri(env('GOOGLE_REDIRECT'));
        $this->client->addScope('https://www.googleapis.com/auth/calendar.events.owned');
        $this->client->addScope(Google_Service_Oauth2::USERINFO_PROFILE);
        $this->client->addScope(Google_Service_Oauth2::USERINFO_EMAIL);
        $this->client->addScope(Google_Service_Oauth2::OPENID);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
    }

    /**
     * @return Google_Client
     */
    public function getClient(): Google_Client
    {
        return $this->client;
    }

    /**
     * Revoke the access token
     */
    public function revokeToken(): bool
    {
        return $this->client->revokeToken();
    }
}
