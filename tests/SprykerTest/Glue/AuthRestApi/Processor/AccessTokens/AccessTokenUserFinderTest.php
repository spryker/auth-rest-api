<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Glue\AuthRestApi\Processor\AccessTokens;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\OauthAccessTokenDataTransfer;
use Spryker\Glue\AuthRestApi\AuthRestApiConfig;
use Spryker\Glue\AuthRestApi\Dependency\Service\AuthRestApiToOauthServiceInterface;
use Spryker\Glue\AuthRestApi\Dependency\Service\AuthRestApiToUtilEncodingServiceBridge;
use Spryker\Glue\AuthRestApi\Processor\AccessTokens\AccessTokenUserFinder;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Spryker\Service\UtilEncoding\UtilEncodingServiceInterface;
use SprykerTest\Glue\AuthRestApi\AuthRestApiTester;
use Symfony\Component\HttpFoundation\Request;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Glue
 * @group AuthRestApi
 * @group Processor
 * @group AccessTokens
 * @group AccessTokenUserFinderTest
 * Add your own group annotations below this line
 */
class AccessTokenUserFinderTest extends Unit
{
    /**
     * @var \SprykerTest\Glue\AuthRestApi\AuthRestApiTester
     */
    protected AuthRestApiTester $tester;

    public function testFindUserReturnsNullWhenJwtSubClaimIsNotJsonObject(): void
    {
        // Arrange: underlying service returns int (what json_decode("1234567890") produces)
        $utilEncodingServiceMock = $this->createMock(UtilEncodingServiceInterface::class);
        $utilEncodingServiceMock->method('decodeJson')->willReturn(1234567890);

        $oauthServiceMock = $this->createMock(AuthRestApiToOauthServiceInterface::class);
        $oauthServiceMock->method('extractAccessTokenData')
            ->willReturn((new OauthAccessTokenDataTransfer())->setOauthUserId('1234567890'));

        $userFinder = new AccessTokenUserFinder(
            $oauthServiceMock,
            new AuthRestApiToUtilEncodingServiceBridge($utilEncodingServiceMock),
            [],
        );

        $httpRequest = Request::create('/', Request::METHOD_GET, [], [], [], [
            'HTTP_' . strtoupper(str_replace('-', '_', AuthRestApiConfig::HEADER_AUTHORIZATION)) => 'Bearer some.jwt.token',
        ]);
        $restRequest = $this->createMock(RestRequestInterface::class);
        $restRequest->method('getHttpRequest')->willReturn($httpRequest);

        // Act
        $result = $userFinder->findUser($restRequest);

        // Assert
        $this->assertNull($result);
    }
}
