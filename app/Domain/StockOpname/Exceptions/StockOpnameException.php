<?php

declare(strict_types=1);

namespace App\Domain\StockOpname\Exceptions;

use DomainException;
use Throwable;

/**
 */
abstract class StockOpnameException extends DomainException
{
    /**
     * @param  array<string, mixed>  $context  Konteks structured terkait kegagalan.
     */
    public function __construct(
        string $message = '',
        public readonly array $context = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
