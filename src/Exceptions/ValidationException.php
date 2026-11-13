<?php
declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class ValidationException extends RuntimeException
{
    /** @param string[] $errors */
    public function __construct(private array $errors)
    {
        parent::__construct(implode(' ', $errors));
    }

    /** @return string[] */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
