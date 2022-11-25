<?php

namespace App\Entities;

use App\Services\SettingService;
use Laravel\Socialite\SocialiteManager as VendorSocialiteManager;

class SocialiteManager extends VendorSocialiteManager
{
    /*
     * @override
     * */
    protected function createFacebookDriver()
    {
        try {
            $config = $this->config->get('services.facebook');

            $oAuthFacebookKeys = app(SettingService::class)
                ->getOAuthFacebookKeys();

            $config['client_id'] = $oAuthFacebookKeys['facebook_client_id'] ?? null;
            $config['client_secret'] = $oAuthFacebookKeys['facebook_client_secret'] ?? null;

            return $this->buildProvider(
                FacebookProvider::class, $config
            );
        } catch (\Throwable $th) {

            return parent::createFacebookDriver();

        }
    }

    /*
     * @override
     * */
    protected function createGoogleDriver()
    {
        try {
            $config = $this->config->get('services.google');

            $oAuthGoogleKeys = app(SettingService::class)
                ->getOAuthGoogleKeys();

            $config['client_id'] = $oAuthGoogleKeys['google_client_id'] ?? null;
            $config['client_secret'] = $oAuthGoogleKeys['google_client_secret'] ?? null;

            return $this->buildProvider(
                GoogleProvider::class, $config
            );
        } catch (\Throwable $th) {

            return parent::createGoogleDriver();

        }
    }

    /*
     * @override
     * */
    protected function createTwitterDriver()
    {
        try {
            $config = $this->config->get('services.twitter');

            $oAuthTwitterKeys = app(SettingService::class)
                ->getOAuthTwitterKeys();

            $config['client_id'] = $oAuthTwitterKeys['twitter_client_id'] ?? null;
            $config['client_secret'] = $oAuthTwitterKeys['twitter_client_secret'] ?? null;

            if (($config['oauth'] ?? null) === 2) {
                return $this->createTwitterOAuth2Driver();
            }

            return new TwitterProvider(
                $this->container->make('request'), new TwitterServer($this->formatConfig($config))
            );
        } catch (\Throwable $th) {

            return parent::createTwitterDriver();

        }
    }
}