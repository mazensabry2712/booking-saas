<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    /**
     * Show public queue dashboard
     */
    public function dashboard()
    {
        return view('queue.dashboard');
    }
}
