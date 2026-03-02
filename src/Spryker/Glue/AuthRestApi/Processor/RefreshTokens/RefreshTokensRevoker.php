<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AuthRestApi\Processor\RefreshTokens;

use Spryker\Glue\AuthRestApi\AuthRestApiConfig;
use Spryker\Glue\AuthRestApi\Dependency\Client\AuthRestApiToOauthClientInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Symfony\Component\HttpFoundation\Response;

class RefreshTokensRevoker implements RefreshTokensRevokerInterface
{
    /**
     * @var \Spryker\Glue\AuthRestApi\Dependency\Client\AuthRestApiToOauthClientInterface
     */
    protected $oauthClient;

    /**
     * @var \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface
     */
    protected $restResourceBuilder;

    public function __construct(
        AuthRestApiToOauthClientInterface $oauthClient,
        RestResourceBuilderInterface $restResourceBuilder
    ) {
        $this->oauthClient = $oauthClient;
        $this->restResourceBuilder = $restResourceBuilder;
    }

    public function revokeRefreshToken(RestRequestInterface $restRequest): RestResponseInterface
    {
        $refreshTokenIdentifier = $restRequest->getResource()->getId();

        if (!$refreshTokenIdentifier) {
            return $this->restResourceBuilder->createRestResponse()
                ->setStatus(Response::HTTP_BAD_REQUEST);
        }

        if ($this->isResourceIdentifierCurrentUser($refreshTokenIdentifier)) {
            return $this->revokeCustomerRefreshTokens($restRequest);
        }

        return $this->revokeRefreshTokenByIdentifier($refreshTokenIdentifier, $restRequest);
    }

    protected function revokeRefreshTokenByIdentifier(string $refreshTokenIdentifier, RestRequestInterface $restRequest): RestResponseInterface
    {
        $naturalIdentifier = $restRequest->getRestUser()?->getNaturalIdentifier() ?? '';

        $this->oauthClient->revokeRefreshToken($refreshTokenIdentifier, $naturalIdentifier);

        return $this->restResourceBuilder->createRestResponse()->setStatus(Response::HTTP_NO_CONTENT);
    }

    protected function revokeCustomerRefreshTokens(RestRequestInterface $restRequest): RestResponseInterface
    {
        $naturalIdentifier = $restRequest->getRestUser()?->getNaturalIdentifier() ?? '';

        $this->oauthClient->revokeAllRefreshTokens($naturalIdentifier);

        return $this->restResourceBuilder->createRestResponse()->setStatus(Response::HTTP_NO_CONTENT);
    }

    protected function isResourceIdentifierCurrentUser(string $resourceIdentifier): bool
    {
        return $resourceIdentifier === AuthRestApiConfig::COLLECTION_IDENTIFIER_CURRENT_USER;
    }
}
