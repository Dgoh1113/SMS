<?php

namespace App\Http\Controllers\Concerns;

use App\Support\UserSetupLinkStore;

trait UsesSetupLinkStore
{
    protected function setupLinkStore(): UserSetupLinkStore
    {
        return app(UserSetupLinkStore::class);
    }
}
