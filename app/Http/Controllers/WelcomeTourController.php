<?php

namespace App\Http\Controllers;

class WelcomeTourController extends Controller
{
    public function show()
    {
        return view('welcome-tour', [
            'categories' => OnboardingController::categories(),
        ]);
    }
}
