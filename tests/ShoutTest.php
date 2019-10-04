<?php

use App\Repositories\QuoteRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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

    public function testShoutReturnsQuotes()
    {
        $this->json('GET', self::ROUTE, ['limit' => 2]);
        $this->seeStatusCode(200);
        $this->seeJson([
            'data' => [
                'THE ONLY WAY TO DO GREAT WORK IS TO LOVE WHAT YOU DO!',
                'YOUR TIME IS LIMITED, SO DON’T WASTE IT LIVING SOMEONE ELSE’S LIFE!'
            ]
        ]);
    }

    public function testLimitReturnedQuotes()
    {
        $this->json('GET', self::ROUTE, ['limit' => 1]);
        $this->seeStatusCode(200);
        $this->seeJson([
            'data' => [
                'THE ONLY WAY TO DO GREAT WORK IS TO LOVE WHAT YOU DO!',
            ]
        ]);
    }

    public function testQuoteCaching()
    {
        $mockRepo = new class extends QuoteRepository {
            public $timesCalled = 0;

            public function __construct()
            {
                parent::__construct(30);
            }

            protected function getQuotesFromStorage()

            {
                ++$this->timesCalled;
                return parent::getQuotesFromStorage();
            }
        };
        $this->app->singleton(QuoteRepository::class, function () use ($mockRepo) {
            return $mockRepo;
        });
        $now = Carbon::now();

        // check cache is used
        Carbon::setTestNow($now);
        $this->json('GET', self::ROUTE, ['limit' => 2]);
        $this->seeStatusCode(200);
        $this->json('GET', self::ROUTE, ['limit' => 2]);
        $this->seeStatusCode(200);
        $this->assertEquals(1, $mockRepo->timesCalled);

        // check cache is invalidated
        Carbon::setTestNow($now->addMinute());
        $this->json('GET', self::ROUTE, ['limit' => 2]);
        $this->seeStatusCode(200);
        $this->assertEquals(2, $mockRepo->timesCalled);

        // check cache is used again
        Carbon::setTestNow($now->addSeconds(29));
        $this->json('GET', self::ROUTE, ['limit' => 2]);
        $this->seeStatusCode(200);
        $this->assertEquals(2, $mockRepo->timesCalled);
    }
}
