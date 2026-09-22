<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Bcrypt must not silently discard bytes beyond its 72-byte limit.
        if (! is_string($value) || mb_strlen($value) < 15 || strlen($value) > 72) {
            $fail('Use a password with at least 15 characters and at most 72 bytes.');
        }
    }
}
