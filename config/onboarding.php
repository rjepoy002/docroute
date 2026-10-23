<?php

return [
    // Exact company domains only. An empty list disables self-onboarding.
    'company_domains' => array_values(array_filter(array_map('trim', explode(',', strtolower(env('ONBOARDING_COMPANY_DOMAINS', '')))))),
];
