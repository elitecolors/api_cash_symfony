<?php

namespace App\Tests\Api;

use \App\Tests\ApiTester;

class RegistrationCest
{
    public function _before(ApiTester $I)
    {
        $I->sendPost('http://localhost:8080/auth/register');
    }

    // tests
    public function tryToTest(ApiTester $I)
    {
    }
}
