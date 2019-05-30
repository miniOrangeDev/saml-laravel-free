<?php
namespace MiniOrange\Helper;

use Illuminate\Support\Facades\DB as LaraDB;
use MiniOrange\Classes\Actions\DatabaseController as DC;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Console\Kernel as Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use PDOException;
use phpDocumentor\Reflection\Types\Null_;

class DB extends Controller
{

    public static function get_option($key)
    {
         try {
             $result = LaraDB::select('select * from mo_config where id = ?', [1])[0];
         }
         catch(\PDOException $e)
         {
             if($e->getCode() == '42S02'){
                 header('Location: create_tables');
                 exit;
             }
         }
        return $result->$key;
    }

    public static function update_option($key, $value)
    {
        $result = LaraDB::table('mo_config')->updateOrInsert([
            'id' => 1
        ],[
            $key => $value
        ]);
    }

    public static function delete_option($key)
    {
        $result = LaraDB::table('mo_config')->updateOrInsert([
            'id' => 1
        ],[
            $key => ''
        ]);
    }

    public static function get_registered_user()
    {
        try {
            $result = LaraDB::select('select * from mo_admin')[0];
        }
        catch(\PDOException $e){
            if($e->getCode() == '42S02'){
                header('Location: create_tables');
                exit;
            }
        }
        if(empty($result->email))
            return null;
        else
            return $result;
    }

    public static function register_user($email, $password)
    {
        LaraDB::table('mo_admin')->updateOrInsert([
            'id' => 1
        ], [
            'email' => $email,
            'password' => $password
        ]);
    }

    protected static function get_options()
    {
        $result = LaraDB::select('select * from mo_config')[0];
    }
}

?>
