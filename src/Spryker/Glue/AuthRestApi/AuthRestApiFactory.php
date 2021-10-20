<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\AuthRestApi;

use Spryker\Glue\AuthRestApi\Dependency\Client\AuthRestApiToOauthClientInterface;
use Spryker\Glue\AuthRestApi\Dependency\Service\AuthRestApiToOauthServiceInterface;
use Spryker\Glue\AuthRestApi\Dependency\Service\AuthRestApiToUtilEncodingServiceInterface;
use Spryker\Glue\AuthRestApi\Processor\AccessTokens\AccessTokensReader;
use Spryker\Glue\AuthRestApi\Processor\AccessTokens\AccessTokensReaderInterface;
use Spryker\Glue\AuthRestApi\Processor\AccessTokens\AccessTokenUserFinder;
use Spryker\Glue\AuthRestApi\Processor\AccessTokens\AccessTokenUserFinderInterface;
use Spryker\Glue\AuthRestApi\Processor\AccessTokens\AccessTokenValidator;
use Spryker\Glue\AuthRestApi\Processor\AccessTokens\AccessTokenValidatorInterface;
use Spryker\Glue\AuthRestApi\Processor\AccessTokens\OauthToken;
use Spryker\Glue\AuthRestApi\Processor\AccessTokens\OauthTokenInterface;
use Spryker\Glue\AuthRestApi\Processor\AccessTokens\SimultaneousAuthenticationRestRequestValidator;
use Spryker\Glue\AuthRestApi\Processor\AccessTokens\SimultaneousAuthenticationRestRequestValidatorInterface;
use Spryker\Glue\AuthRestApi\Processor\RefreshTokens\RefreshTokensReader;
use Spryker\Glue\AuthRestApi\Processor\RefreshTokens\RefreshTokensReaderInterface;
use Spryker\Glue\AuthRestApi\Processor\RefreshTokens\RefreshTokensRevoker;
use Spryker\Glue\AuthRestApi\Processor\RefreshTokens\RefreshTokensRevokerInterface;
use Spryker\Glue\AuthRestApi\Processor\ResponseFormatter\AuthenticationErrorResponseHeadersFormatter;
use Spryker\Glue\Kernel\AbstractFactory;

/**
 * @method \Spryker\Client\AuthRestApi\AuthRestApiClientInterface getClient()
 * @method \Spryker\Glue\AuthRestApi\AuthRestApiConfig getConfig()
 */
class AuthRestApiFactory extends AbstractFactory
{
    /**
     * @return \Spryker\Glue\AuthRestApi\Processor\AccessTokens\AccessTokensReaderInterface
     */
    public function createAccessTokensReader(): AccessTokensReaderInterface
    {
        return new AccessTokensReader(
            $this->getClient(),
            $this->getResourceBuilder(),
            $this->getConfig(),
        );
    }

    /**
     * @return \Spryker\Glue\AuthRestApi\Processor\RefreshTokens\RefreshTokensReaderInterface
     */
    public function createRefreshTokensReader(): RefreshTokensReaderInterface
    {
        return new RefreshTokensReader(
            $this->getOauthClient(),
            $this->getResourceBuilder(),
            $this->getConfig(),
        );
    }

    /**
     * @return \Spryker\Glue\AuthRestApi\Processor\RefreshTokens\RefreshTokensRevokerInterface
     */
    public function createRefreshTokenRevoker(): RefreshTokensRevokerInterface
    {
        return new RefreshTokensRevoker(
            $this->getOauthClient(),
            $this->getResourceBuilder(),
        );
    }

    /**
     * @return \Spryker\Glue\AuthRestApi\Processor\AccessTokens\AccessTokenValidatorInterface
     */
    public function createAccessTokenValidator(): AccessTokenValidatorInterface
    {
        return new AccessTokenValidator($this->getOauthClient());
    }

    /**
     * @return \Spryker\Glue\AuthRestApi\Processor\ResponseFormatter\AuthenticationErrorResponseHeadersFormatter
     */
    public function createAuthenticationErrorResponseHeadersFormatter(): AuthenticationErrorResponseHeadersFormatter
    {
        return new AuthenticationErrorResponseHeadersFormatter();
    }

    /**
     * @return \Spryker\Glue\AuthRestApi\Processor\AccessTokens\AccessTokenUserFinderInterface
     */
    public function createAccessTokenUserFinder(): AccessTokenUserFinderInterface
    {
        return new AccessTokenUserFinder(
            $this->getOauthService(),
            $this->getUtilEncodingService(),
            $this->getRestUserExpanderPlugins(),
        );
    }

    /**
     * @return \Spryker\Glue\AuthRestApi\Processor\AccessTokens\OauthTokenInterface
     */
    public function createOauthToken(): OauthTokenInterface
    {
        return new OauthToken($this->getClient());
    }

    /**
     * @return \Spryker\Glue\AuthRestApi\Processor\AccessTokens\SimultaneousAuthenticationRestRequestValidatorInterface
     */
    public function createSimultaneousAuthenticationRestRequestValidator(): SimultaneousAuthenticationRestRequestValidatorInterface
    {
        return new SimultaneousAuthenticationRestRequestValidator();
    }

    /**
     * @return \Spryker\Glue\AuthRestApi\Dependency\Client\AuthRestApiToOauthClientInterface
     */
    public function getOauthClient(): AuthRestApiToOauthClientInterface
    {
        return $this->getProvidedDependency(AuthRestApiDependencyProvider::CLIENT_OAUTH);
    }

    /**
     * @return \Spryker\Glue\AuthRestApi\Dependency\Service\AuthRestApiToOauthServiceInterface
     */
    public function getOauthService(): AuthRestApiToOauthServiceInterface
    {
        return $this->getProvidedDependency(AuthRestApiDependencyProvider::SERVICE_OAUTH);
    }

    /**
     * @return \Spryker\Glue\AuthRestApi\Dependency\Service\AuthRestApiToUtilEncodingServiceInterface
     */
    public function getUtilEncodingService(): AuthRestApiToUtilEncodingServiceInterface
    {
        return $this->getProvidedDependency(AuthRestApiDependencyProvider::SERVICE_UTIL_ENCODING);
    }

    /**
     * @return array<\Spryker\Glue\AuthRestApiExtension\Dependency\Plugin\RestUserMapperPluginInterface>
     */
    public function getRestUserExpanderPlugins(): array
    {
        return $this->getProvidedDependency(AuthRestApiDependencyProvider::PLUGINS_REST_USER_EXPANDER);
    }
}
