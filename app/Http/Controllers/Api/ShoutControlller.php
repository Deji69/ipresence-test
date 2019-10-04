<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\QuoteRepository;

class ShoutController extends Controller
{
    /** @var QuoteRepository */
    private $quoteRepo;

    public function __construct(QuoteRepository $quoteRepo)
    {
        $this->quoteRepo = $quoteRepo;
    }

    public function shout(string $author, Request $request)
    {
        $this->validate($request, [
            'limit' => 'numeric|min:1|max:10'
        ]);
        $limit = (int)$request->input('limit', 10) ?: 10;
        return ['data' => $this->quoteRepo->getQuotesShouted($author, $limit)];
    }
}
