<?php

namespace App\Http\Controllers;

use File;
use Illuminate\Http\Request;

class LogsController extends Controller
{
    public function index()
    {
        $files = scandir(storage_path('logs'));
        $result = [];
        foreach ($files as $file) {
            if (stripos($file, 'log') !== false) {
                $result[] = $file;
            }
        }
        $files = $result;
        unset($result);

        return view('logs.index', compact('files'));
    }

    public function download($filename)
    {
        return response()->download(storage_path('logs').'/'.$filename);
    }
}
