<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AuthRestApi\Controller;

use Generated\Shared\Transfer\OauthRequestTransfer;
use Spryker\Client\AuthRestApi\AuthRestApiClient;
use Spryker\Glue\Kernel\Controller\FormatAbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TokenResourceController extends FormatAbstractController
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $httpRequest
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function postAction(Request $httpRequest): JsonResponse
    {
        $oauthRequestTransfer = (new OauthRequestTransfer())->fromArray($httpRequest->request->all());

        $authRestApiClient = new AuthRestApiClient();
        $oauthResponseTransfer = $authRestApiClient->createAccessToken($oauthRequestTransfer);

        $response = new JsonResponse();
        if (!$oauthResponseTransfer->getIsValid()) {
            /**
             * https://tools.ietf.org/html/rfc6749#section-5.2
             */
            return $response->setData([
                'error' => $oauthResponseTransfer->getError()->getErrorType(),
                'error_description' => $oauthResponseTransfer->getError()->getMessage(),
            ])->setStatusCode(400);
        }

        /**
         * https://tools.ietf.org/html/rfc6749#section-5.1
         */
        return $response->setData([
            'access_token' => $oauthResponseTransfer->getAccessToken(),
            'token_type' => $oauthResponseTransfer->getTokenType(),
            'expires_in' => $oauthResponseTransfer->getExpiresIn(),
            'refresh_token' => $oauthResponseTransfer->getRefreshToken(),
        ])->setStatusCode(200);
    }
}
