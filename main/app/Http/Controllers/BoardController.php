<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Traits\ApiResponser;

class BoardController extends Controller
{
    use ApiResponser;
    public function getData()
    {
        return $this->success(Board::get());
    }
}
