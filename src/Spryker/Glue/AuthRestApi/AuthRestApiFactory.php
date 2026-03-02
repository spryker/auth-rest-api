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
use Spryker\Glue\AuthRestApi\Processor\Logger\AuditLogger;
use Spryker\Glue\AuthRestApi\Processor\Logger\AuditLoggerInterface;
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
    public function createAccessTokensReader(): AccessTokensReaderInterface
    {
        return new AccessTokensReader(
            $this->getClient(),
            $this->getResourceBuilder(),
            $this->getConfig(),
            $this->createAuditLogger(),
        );
    }

    public function createRefreshTokensReader(): RefreshTokensReaderInterface
    {
        return new RefreshTokensReader(
            $this->getOauthClient(),
            $this->getResourceBuilder(),
            $this->getConfig(),
        );
    }

    public function createRefreshTokenRevoker(): RefreshTokensRevokerInterface
    {
        return new RefreshTokensRevoker(
            $this->getOauthClient(),
            $this->getResourceBuilder(),
        );
    }

    public function createAccessTokenValidator(): AccessTokenValidatorInterface
    {
        return new AccessTokenValidator($this->getOauthClient());
    }

    public function createAuthenticationErrorResponseHeadersFormatter(): AuthenticationErrorResponseHeadersFormatter
    {
        return new AuthenticationErrorResponseHeadersFormatter();
    }

    public function createAccessTokenUserFinder(): AccessTokenUserFinderInterface
    {
        return new AccessTokenUserFinder(
            $this->getOauthService(),
            $this->getUtilEncodingService(),
            $this->getRestUserExpanderPlugins(),
        );
    }

    public function createOauthToken(): OauthTokenInterface
    {
        return new OauthToken($this->getClient(), $this->createAuditLogger());
    }

    public function createSimultaneousAuthenticationRestRequestValidator(): SimultaneousAuthenticationRestRequestValidatorInterface
    {
        return new SimultaneousAuthenticationRestRequestValidator();
    }

    public function createAuditLogger(): AuditLoggerInterface
    {
        return new AuditLogger();
    }

    public function getOauthClient(): AuthRestApiToOauthClientInterface
    {
        return $this->getProvidedDependency(AuthRestApiDependencyProvider::CLIENT_OAUTH);
    }

    public function getOauthService(): AuthRestApiToOauthServiceInterface
    {
        return $this->getProvidedDependency(AuthRestApiDependencyProvider::SERVICE_OAUTH);
    }

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
