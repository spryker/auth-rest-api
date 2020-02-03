<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AuthRestApi\Processor\RefreshTokens;

use Generated\Shared\Transfer\RevokeRefreshTokenRequestTransfer;
use Spryker\Glue\AuthRestApi\Dependency\Client\AuthRestApiToCustomerClientInterface;
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
     * @var \Spryker\Glue\AuthRestApi\Dependency\Client\AuthRestApiToCustomerClientInterface
     */
    protected $customerClient;

    /**
     * @var \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface
     */
    protected $restResourceBuilder;

    /**
     * @param \Spryker\Glue\AuthRestApi\Dependency\Client\AuthRestApiToOauthClientInterface $oauthClient
     * @param \Spryker\Glue\AuthRestApi\Dependency\Client\AuthRestApiToCustomerClientInterface $customerClient
     * @param \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface $restResourceBuilder
     */
    public function __construct(
        AuthRestApiToOauthClientInterface $oauthClient,
        AuthRestApiToCustomerClientInterface $customerClient,
        RestResourceBuilderInterface $restResourceBuilder
    ) {
        $this->oauthClient = $oauthClient;
        $this->customerClient = $customerClient;
        $this->restResourceBuilder = $restResourceBuilder;
    }

    /**
     * @param string $refreshTokenIdentifier
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function revokeConcreteRefreshToken(string $refreshTokenIdentifier, RestRequestInterface $restRequest): RestResponseInterface
    {
        $restResponse = $this->restResourceBuilder->createRestResponse();

        $customer = $this->customerClient->getCustomerById($restRequest->getRestUser()->getSurrogateIdentifier());

        $revokeRefreshTokenRequestTransfer = (new RevokeRefreshTokenRequestTransfer())
            ->setRefreshToken($refreshTokenIdentifier)
            ->setCustomer($customer);

        $this->oauthClient->revokeConcreteRefreshToken($revokeRefreshTokenRequestTransfer);

        return $restResponse->setStatus(Response::HTTP_NO_CONTENT);
    }

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function revokeAllCurrentCustomerRefreshTokens(RestRequestInterface $restRequest): RestResponseInterface
    {
        $restResponse = $this->restResourceBuilder->createRestResponse();

        $customer = $this->customerClient->getCustomerById($restRequest->getRestUser()->getSurrogateIdentifier());

        $revokeRefreshTokenRequestTransfer = (new RevokeRefreshTokenRequestTransfer())
            ->setCustomer($customer);

        $this->oauthClient->revokeRefreshTokensByCustomer($revokeRefreshTokenRequestTransfer);

        return $restResponse->setStatus(Response::HTTP_NO_CONTENT);
    }
}