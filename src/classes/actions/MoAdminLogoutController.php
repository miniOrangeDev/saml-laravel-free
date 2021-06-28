<?php

namespace MiniOrange\Classes\Actions;

use Illuminate\Routing\Controller;

class MoAdminLogoutController extends Controller {
    public function launch() {
        include_once dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'admin_logout.php';
    }
}