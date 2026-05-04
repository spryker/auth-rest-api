<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\AuthRestApi\Api\Storefront\Exception;

use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\Glue\AuthRestApi\AuthRestApiConfig;
use Symfony\Component\HttpFoundation\Response;

class AuthRestApiExceptionFactory
{
    protected const string ERROR_MESSAGE_INVALID_LOGIN = 'Failed to authenticate user.';

    protected const string ERROR_MESSAGE_REFRESH_FAILED = 'Failed to refresh token.';

    public function createInvalidLoginException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNAUTHORIZED,
            AuthRestApiConfig::RESPONSE_INVALID_LOGIN,
            static::ERROR_MESSAGE_INVALID_LOGIN,
        );
    }

    public function createInvalidRefreshTokenException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNAUTHORIZED,
            AuthRestApiConfig::RESPONSE_INVALID_REFRESH_TOKEN,
            static::ERROR_MESSAGE_REFRESH_FAILED,
        );
    }

    public function createMissingAccessTokenException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_FORBIDDEN,
            AuthRestApiConfig::RESPONSE_CODE_FORBIDDEN,
            AuthRestApiConfig::RESPONSE_DETAIL_MISSING_ACCESS_TOKEN,
        );
    }
}
