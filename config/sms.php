<?php

return [
    'allowed_domains' => explode(',', env('SMS_ALLOWED_DOMAINS', 'institution.edu')),
    'require_admin_approval' => env('SMS_REQUIRE_ADMIN_APPROVAL', true),
    'institution_name' => env('SMS_INSTITUTION_NAME', 'Institution Name'),
    'pdf_paper_size' => env('SMS_PDF_PAPER_SIZE', 'A4'),
];
