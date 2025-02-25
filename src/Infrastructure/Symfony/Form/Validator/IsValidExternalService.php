<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Symfony\Form\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class IsValidExternalService extends Constraint
{
    public const string IS_VALID_EXTERNAL_SERVICE_ERROR = '361e8707-6e36-43db-86fa-2a7955af9bac';

    public string $message = 'This value is not a properly configured external service.';
}
