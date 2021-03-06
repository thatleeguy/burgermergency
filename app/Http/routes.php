<?php

Route::get('/', function () {
    return view('burgermergency')
        ->with('results', null)
        ->with('shops', collect([]))
        ->with('search', null);
});

Route::get('{search}', function ($search, App\YelpClient $yelp) {
    $minutes = 30;

    $results = Cache::remember('yelp:' . $search, $minutes, function () use ($yelp, $search) {
        if (substr($search, 0, 7) == 'latlon:') {
            return $yelp->searchByLl(substr($search, 7), ['term' => 'Burgers']);
        }

        return $yelp->search(['term' => 'Burgers', 'location' => $search]);
    });

    // Only businesses that have not been permanently closed
    $shops = collect($results->businesses)->reject(function ($shop) {
        return $shop->is_closed;
    });

    return view('burgermergency')
        ->with('results', $results)
        ->with('shops', $shops)
        ->with('search', $search);
});

Route::post('search', function (Illuminate\Http\Request $request) {
    return redirect('/' . $request->input('location'));
});
