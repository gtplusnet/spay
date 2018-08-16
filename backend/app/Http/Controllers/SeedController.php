<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Globals\Seed;

class SeedController extends Controller
{
    function index()
    {
        Seed::coin();
    }

    function test_seed()
    {
        Seed::test_seed();
    }
}