<?php

namespace App\Shared\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class StudentShellPreviewController extends Controller
{
    public function __invoke(): View
    {
        return view('student.preview');
    }
}
