<?php

use Laravel\Lumen\Testing\DatabaseTransactions;

class ShoutTest extends TestCase
{
    const ROUTE = 'api/v1/shout/steve-jobs';

    public function testRequestShout()
    {
        $this->json('GET', self::ROUTE);
        $this->seeStatusCode(200);
    }

    public function testShoutValidatesLimit()
    {
        // invalidates if provided limit is less than 1
        $this->json('GET', self::ROUTE, ['limit' => 0]);
        $this->seeStatusCode(422);
        $this->seeJson([
            'errors' => [
                'limit' => ['The limit must be at least 1.']
            ]
        ]);

        // invalidates if provided limit is more than 10
        $this->json('GET', self::ROUTE, ['limit' => 11]);
        $this->seeStatusCode(422);
        $this->seeJson([
            'errors' => [
                'limit' => ['The limit may not be greater than 10.']
            ]
        ]);

        // invalidates if provided limit is not a number
        $this->json('GET', self::ROUTE, ['limit' => 'a']);
        $this->seeStatusCode(422);
        $this->seeJson([
            'errors' => [
                'limit' => ['The limit must be a number.']
            ]
        ]);
    }
}
