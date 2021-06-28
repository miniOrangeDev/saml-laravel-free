<?php

namespace MiniOrange\Classes\Actions;

use Illuminate\Routing\Controller;

class MoHowToSetupController extends Controller {
    public function launch() {
        include_once dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'how_to_setup.php';
        include_once dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'jsLoader.php';
        return view('mosaml::howToSetupView');
    }
}