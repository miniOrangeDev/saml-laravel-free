<?php
namespace MiniOrange\Helper;

use Illuminate\Database\Capsule\Manager as LaraDB;
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
        self::startConnection();
        $option = LaraDB::table('mo_config')->first()->$key;
        return $option;
    }

    public static function update_option($key, $value)
    {
        self::startConnection();
        LaraDB::table('mo_config')->where('id', 1)->update([
            $key => $value
        ]);
    }

    public static function delete_option($key)
    {
        self::startConnection();
        LaraDB::table('mo_config')->where('id', 1)->update([
            $key => ''
        ]);
    }

    protected static function get_options()
    {
        self::startConnection();
        $active_config = LaraDB::table('mo_config')->get()->first();
        return $active_config;
    }

    public static function get_registered_user()
    {
        self::startConnection();
        $registered_user = LaraDB::table('mo_admin')->get()->first();
        if($registered_user !== NULL )
            {return $registered_user;}
        else
            { if(isset($_SESSION['authorized'])) {
            unset($_SESSION['authorized']);
            header('Location: mo_admin');
            exit;}
            }
    }

    public static function register_user($email, $password)
    {
        self::startConnection();
        LaraDB::table('mo_admin')->updateOrInsert([
            'id' => 1
        ], [
            'email' => $email,
            'password' => $password
        ]);
    }

    protected static function startConnection()
    {
        $connection = array(
            'driver' => getenv('DB_CONNECTION'),
            'host' => getenv('DB_HOST'),
            'port' => getenv('DB_PORT'),
            'database' => getenv('DB_DATABASE'),
            'username' => getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD')
        );

        $Capsule = new LaraDB();
        $Capsule->addConnection($connection);
        $Capsule->setAsGlobal(); // this is important. makes the database manager object globally available
        $Capsule->bootEloquent();
        try {

            if (LaraDB::table('mo_config')->get()->first() == NULL) {
                LaraDB::table('mo_config')->updateOrInsert([
                    'id' => 1
                ], [
                    'mo_saml_host_name' => 'https://auth.miniorange.com'
                ]);
            }
        } catch (PDOException $e) {

            if ($e->getCode() === '42S02') {

                header('Location: create_tables');
                exit();
            }
            if ($e->getCode() == 2002) {
                echo 'It looks like your <b>Database is offline</b>. Please make sure that your database is up and running, and try again.<a style="text-decoration:none" href="/"><u>Click here to go back to your website</u></a>';
                exit();
            }
        }
    }
}
?>
