<?php

return [
    // Number of digits in the verification code sent by "forgot password".
    'code_length' => 6,

    // Minutes the code stays valid.
    'ttl_minutes' => (int) env('RESET_CODE_TTL_MINUTES', 10),

    // Wrong guesses allowed per code; after that the code is dead and a new one must be requested.
    'max_attempts' => 5,

    // Seconds before the same email can be sent another code (stops email bombing).
    'resend_cooldown_seconds' => 60,
];
