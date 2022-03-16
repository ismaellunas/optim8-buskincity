<?php

namespace App\Entities;

use App\Models\User;
use Stripe\Account;

class UserMetaStripe
{
    public User $meta;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function setAccount(Account $stripeAccount)
    {
        $this->user->setMeta('stripe_account', $stripeAccount);
        $this->user->saveMetas();
    }

    public function hasAccount(): bool
    {
        return $this->user->getMetas(['stripe_account_id'])->isNotEmpty();
    }

    public function getAccountId(): ?string
    {
        return $this->user->getMetas(['stripe_account_id'])->first();
    }

    private function setAccountId(string $stripeAccountId)
    {
        $this->user->setMeta('stripe_account_id', $stripeAccountId);
        $this->user->saveMetas();
    }

    public function setEnabledStatus(bool $isEnabled)
    {
        $this->user->setMeta('stripe_is_enabled', $isEnabled);
        $this->user->saveMetas();
    }

    public function initConnectedAccount(Account $stripeAccount)
    {
        $this->setAccount($stripeAccount);
        $this->setAccountId($stripeAccount->id);
        $this->setEnabledStatus(true);
    }
}
