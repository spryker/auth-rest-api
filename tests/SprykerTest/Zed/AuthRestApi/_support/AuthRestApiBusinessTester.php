<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\AuthRestApi;

use Codeception\Actor;
use Generated\Shared\DataBuilder\OauthRequestBuilder;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\OauthRequestTransfer;

/**
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 * @method \Spryker\Zed\AuthRestApi\Business\AuthRestApiFacade getFacade()
 *
 * @SuppressWarnings(PHPMD)
 */
class AuthRestApiBusinessTester extends Actor
{
    use _generated\AuthRestApiBusinessTesterActions;

    /**
     * @var string
     */
    protected const TEST_CUSTOMER_REFERENCE = 'DE--666';

    /**
     * @var string
     */
    protected const TEST_ANONYMOUS_CUSTOMER_REFERENCE = 'anonymous:DE--666';

    /**
     * @var string
     */
    protected const TEST_GRANT_TYPE = 'password';

    /**
     * @var string
     */
    protected const TEST_USERNAME = 'test username';

    /**
     * @var string
     */
    protected const TEST_PASSWORD = 'test password';

    /**
     * @return \Generated\Shared\Transfer\OauthRequestTransfer
     */
    public function prepareOauthRequestTransfer(): OauthRequestTransfer
    {
        $customerTransfer = $this->haveCustomer([
            CustomerTransfer::USERNAME => static::TEST_USERNAME,
            CustomerTransfer::PASSWORD => static::TEST_PASSWORD,
            CustomerTransfer::NEW_PASSWORD => static::TEST_PASSWORD,
        ]);

        return (new OauthRequestBuilder(
            [
                'customerReference' => static::TEST_ANONYMOUS_CUSTOMER_REFERENCE,
                'grantType' => static::TEST_GRANT_TYPE,
                'username' => $customerTransfer->getEmail(),
                'password' => $customerTransfer->getNewPassword(),
            ],
        ))->build();
    }

    /**
     * @return \Generated\Shared\Transfer\OauthRequestTransfer
     */
    public function prepareOauthRequestTransferWithoutCustomerData(): OauthRequestTransfer
    {
        return (new OauthRequestBuilder(
            [
                'customerReference' => static::TEST_ANONYMOUS_CUSTOMER_REFERENCE,
                'grantType' => static::TEST_GRANT_TYPE,
            ],
        ))->build();
    }
}
