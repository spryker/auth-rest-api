<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AuthRestApi\Processor\ResponseFormatter;

use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationErrorResponseHeadersFormatter implements AuthenticationErrorResponseHeadersFormatterInterface
{
    public function format(
        Response $httpResponse,
        RestResponseInterface $restResponse
    ): Response {
        if (count($restResponse->getErrors()) === 0) {
            return $httpResponse;
        }

        if (!$this->hasAuthorizationError($restResponse)) {
            return $httpResponse;
        }

        $httpResponse->headers->set('WWW-Authenticate', 'Bearer realm="Access to shop API"');

        return $httpResponse;
    }

    protected function hasAuthorizationError(RestResponseInterface $restResponse): bool
    {
        foreach ($restResponse->getErrors() as $restErrorMessageTransfer) {
            if ($restErrorMessageTransfer->getStatus() === Response::HTTP_UNAUTHORIZED) {
                return true;
            }
        }

        return false;
    }
}
