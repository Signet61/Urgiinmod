<?php

namespace App\Http\Controllers;

class GameController extends Controller
{
    public function index()
    {
        $members = auth()->check()
            ? auth()->user()->familyMembers()->orderBy('id')->get()
            : collect();

        return view('game', compact('members'));
    }
}
