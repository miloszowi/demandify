<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Authentication;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class OAuth2Token extends AbstractToken implements TokenInterface {}
