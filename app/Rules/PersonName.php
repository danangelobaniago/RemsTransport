<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * First / last names must contain at least two letters in a row, so
 * single-letter entries like "r" or "j g" are rejected for accounts and
 * passenger manifests. Allowed characters are still enforced separately
 * by each form's regex rule.
 */
class PersonName implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || !preg_match('/\p{L}{2}/u', $value)) {
            $fail('First and last names must have at least 2 letters (e.g. "Jo", not "J").');
        }
    }
}
