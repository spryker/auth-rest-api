<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AuthRestApi\Processor\RefreshTokens;

use Generated\Shared\Transfer\RestRefreshTokensAttributesTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;

interface RefreshTokensReaderInterface
{
    public function processAccessTokenRequest(RestRefreshTokensAttributesTransfer $restRefreshTokenAttributesTransfer): RestResponseInterface;
}
