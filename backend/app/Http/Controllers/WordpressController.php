<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Http;

class WordpressController extends Controller
{
    public function getPluginDetails(){
        $plugin_info = Http::get('https://api.wordpress.org/plugins/info/1.0/simply-schedule-appointments.json');
        $json_result = json_decode($plugin_info);
        $installations = $json_result->downloaded;
        $version = $json_result->version;
        $ratings = $json_result->ratings;
        $five_star_ratings = $ratings->{5};
        $other_ratings = $ratings->{4} + $ratings->{3} + $ratings->{2} + $ratings->{1};
        $date = date('Y-m-d');

        return [$five_star_ratings,$other_ratings,$installations,$version, $date];
    }
}