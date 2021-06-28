<?php

namespace MiniOrange\Classes\Actions;

use Illuminate\Routing\Controller;

class MoSupportController extends Controller {
    public function launch() {
        include_once dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'support.php';
        include_once dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'jsLoader.php';
        return view('mosaml::supportView');
    }
}