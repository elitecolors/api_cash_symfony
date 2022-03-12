<?php

namespace App\Tests\Api;

use \App\Tests\ApiTester;

class RegistrationCest
{
    public function _before(ApiTester $I)
    {
        $I->amGoingTo('create a new user');

    }

    // tests
    public function tryToTest(ApiTester $I)
    {
        $I->sendPost('/auth/register', [
            'role' => 'ROLE_ADMIN',
            'email' => 'mailxx@mail.com',
            'password' => 'test'
        ]);

        $I->expectTo('see 200 ok response');

        $I->seeResponseCodeIs(200);


        $I->amGoingTo('try with the same email');

        $I->expectTo('see error');
        $I->sendPost('/auth/register', [
            'role' => 'ROLE_ADMIN',
            'email' => 'mailxx@mail.com',
            'password' => 'test'
        ]);

        $I->seeResponseCodeIs(403);
    }
}
