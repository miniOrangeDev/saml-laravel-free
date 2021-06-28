<?php

namespace MiniOrange\Classes\Actions;

use Illuminate\Routing\Controller;

class MoAdminLoginController extends Controller {
    public function launch() {
        include_once dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'admin_login.php';
        include_once dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'jsLoader.php';
        return view('mosaml::adminLoginView');
    }
}