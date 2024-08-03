<?php

use App\Http\Controllers\GeneratorController;
use App\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;

Route::any('/gaskan', TemplateController::class);
Route::any('/generate', GeneratorController::class);
