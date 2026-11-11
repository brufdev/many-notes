<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

final class InvalidVaultPath extends HttpException
{
    public function __construct()
    {
        parent::__construct(422, 'This path has an invalid name.');
    }
}
