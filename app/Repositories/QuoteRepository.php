<?php
namespace App\Repositories;

use Illuminate\Support\Facades\Cache;

class QuoteRepository
{
    private $quoteCacheTtl;

    public function __construct(int $quoteCacheTtl = 60)
    {
        $this->quoteCacheTtl = $quoteCacheTtl;
    }

    /**
     * Get all quotes from the database.
     *
     * @param  bool $fresh If TRUE, skips the cahce layer.
     * @return \Illuminate\Support\Collection
     */
    public function getAllQuotes(bool $fresh = false)
    {
        $getQuotes = function() {
            $data = \file_get_contents(\storage_path('data/quotes.json'));
            $data = \collect(\json_decode($data)->quotes);
            $data->transform(function ($item) {
                $item->author = \trim($item->author, " \t\r\n\0\x0B-");
                $item->author_key = \strtolower(
                    \preg_replace('/\s+/', '-', $item->author)
                );
                return $item;
            });
            return $data->sortBy('quote');
        };

        if ($fresh) {
            return $getQuotes();
        }

        return Cache::remember('quotes', $this->quoteCacheTtl, $getQuotes);
    }

    /**
     * Get quotes by a certain author.
     *
     * @param  string $author Author to return quotes for.
     * @param  int    $limit  Maximum number of quotes to return.
     * @return \Illuminate\Support\Collection
     */
    public function getQuotesByAuthor(string $author, int $limit)
    {
        return $this->getAllQuotes()
                    ->where('author_key', $author)
                    ->take($limit)
                    ->pluck('quote');
    }

    /**
     * Gets quotes by a certain author SHOUTED.
     *
     * @param  string $author Author to return quotes for.
     * @param  int    $limit  Maximum number of quotes to return
     * @return \Illuminate\Support\Collection
     */
    public function getQuotesShouted(string $author, int $limit)
    {
        return $this->getQuotesByAuthor($author, $limit)
                    ->map(function ($quote) {
                        $quote = \str_replace('.', '!', $quote);
                        return \mb_strtoupper($quote);
                    });
    }
}
