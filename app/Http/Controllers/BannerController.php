<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BannerController extends BaseController
{
    public function index()
    {
        $data = [
            [
                'src' => asset('images/banner1.png'),
            ],
            [
                'src' => asset('images/banner2.png'),
            ],
            [
                'src' => asset('images/banner2.png'),
            ],
            [
                'src' => asset('images/banner1.png'),
            ],
        ];

        return $this->sendResponse($data, 'Fetched');
    }
}
