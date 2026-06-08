<?php

// Reuse the package's publishable migration so the workbench schema never drifts.
return require __DIR__.'/../../../database/migrations/create_sso_user_providers_table.php.stub';
