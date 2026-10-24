<?php

namespace CleaniqueCoders\ConfigSso\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * An SSO sign-in matched a local account by email that had never been verified, and the package
 * revoked the password and remember token before signing the SSO owner in. Listen for it to
 * revoke anything else a registrant could have set up: 2FA secrets, API tokens, passkeys.
 */
class UnverifiedAccountClaimed
{
    use Dispatchable;

    public function __construct(public Authenticatable $user) {}
}
