<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GroupController extends Controller
{
    // グループ作成画面を表示
    public function create()
    {
        return view('group.create');
    }
}