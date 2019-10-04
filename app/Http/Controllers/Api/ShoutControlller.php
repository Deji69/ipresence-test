<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShoutController extends Controller
{
    public function shout(string $author, Request $request)
    {
        $this->validate($request, [
            'limit' => 'numeric|min:1|max:10'
        ]);
        $limit = $request->input('limit', 10);
        return 'hi';
    }
}
