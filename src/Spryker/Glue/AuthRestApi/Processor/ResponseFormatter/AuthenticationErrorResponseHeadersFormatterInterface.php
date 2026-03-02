<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AuthRestApi\Processor\ResponseFormatter;

use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Symfony\Component\HttpFoundation\Response;

interface AuthenticationErrorResponseHeadersFormatterInterface
{
    public function format(
        Response $httpResponse,
        RestResponseInterface $restResponse
    ): Response;
}
