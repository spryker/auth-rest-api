<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AuthRestApi\Controller;

use Generated\Shared\Transfer\RestRefreshTokensAttributesTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Spryker\Glue\Kernel\Controller\AbstractController;

/**
 * @method \Spryker\Glue\AuthRestApi\AuthRestApiFactory getFactory()
 */
class RefreshTokensResourceController extends AbstractController
{
    /**
     * @Glue({
     *     "post": {
     *          "summary": [
     *              "Refreshes customer's auth token."
     *          ],
     *          "parameters": [{
     *              "ref": "acceptLanguage"
     *          }],
     *          "responseAttributesClassName": "Generated\\Shared\\Transfer\\RestTokenResponseAttributesTransfer",
     *          "responses": {
     *              "400": "Bad request.",
     *              "401": "Failed to authenticate user.",
     *              "422": "Unprocessable entity."
     *          },
     *          "isIdNullable": true
     *     }
     * })
     *
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     * @param \Generated\Shared\Transfer\RestRefreshTokensAttributesTransfer $restRefreshTokensAttributesTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function postAction(
        RestRequestInterface $restRequest,
        RestRefreshTokensAttributesTransfer $restRefreshTokensAttributesTransfer
    ): RestResponseInterface {
        return $this->getFactory()
            ->createRefreshTokensReader()
            ->processAccessTokenRequest($restRefreshTokensAttributesTransfer);
    }

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function getAction(RestRequestInterface $restRequest): RestResponseInterface
    {
        return $this->getFactory()->getResourceBuilder()->createRestResponse();
    }

    /**
     * @Glue({
     *     "delete": {
     *          "summary": [
     *              "Revokes customer's refresh token."
     *          ],
     *          "parameters": [{
     *              "name": "Accept-Language",
     *              "in": "header",
     *              "description": "Locale value relevant for the store."
     *          }],
     *          "responses": {
     *              "204": "No content."
     *          }
     *     }
     * })
     *
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function deleteAction(RestRequestInterface $restRequest): RestResponseInterface
    {
        return $this->getFactory()->createRefreshTokenRevoker()->revokeRefreshToken($restRequest);
    }
}
