<?php

use Laravel\Lumen\Testing\DatabaseTransactions;

class ShoutTest extends TestCase
{
    public function testRequestShout()
    {
        $response = $this->call('GET', 'api/v1/shout/steve-jobs', ['limit' => 1]);
        $this->assertEquals(200, $response->status());
    }
}
