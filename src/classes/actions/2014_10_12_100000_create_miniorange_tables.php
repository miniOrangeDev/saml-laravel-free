<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use MiniOrange\Helper\Lib\AESEncryption;

class CreateMiniorangeTables extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mo_config', function (Blueprint $table) {
            $table->string('id', 10)->unique()->nullable();
            $table->text('mo_saml_host_name', 255)->nullable();
            $table->text('mo_saml_admin_email', 255)->nullable();
            $table->text('mo_saml_admin_password', 255)->nullable();
            $table->text('mo_saml_admin_customer_key', 255)->nullable();
            $table->text('mo_saml_admin_api_key', 255)->nullable();
            $table->text('mo_saml_customer_token', 255)->nullable();
            $table->text('mo_saml_free_version', 255)->nullable();
            $table->text('mo_saml_message', 300)->nullable();
            $table->text('idp_entity_id', 255)->nullable();
            $table->text('saml_login_url', 255)->nullable();
            $table->text('saml_login_binding_type', 255)->nullable();
            $table->text('sp_base_url', 255)->nullable();
            $table->text('sp_entity_id', 255)->nullable();
            $table->text('acs_url', 255)->nullable();
            $table->text('single_logout_url', 255)->nullable();
            $table->text('saml_am_email', 255)->nullable();
            $table->text('saml_am_username', 255)->nullable();
            $table->text('relaystate_url', 255)->nullable();
            $table->text('site_logout_url', 255)->nullable();
            $table->text('saml_x509_certificate', 5000)->nullable();
            $table->text('mo_saml_new_registration', 10)->nullable();
            $table->text('mo_saml_admin_phone', 20)->nullable();
            $table->text('mo_saml_verify_customer', 10)->nullable();
            $table->text('mo_saml_idp_config_complete', 255)->nullable();
            $table->text('mo_saml_transactionId', 255)->nullable();
            $table->text('mo_saml_guest_enabled', 10)->nullable();
            $table->text('mo_saml_registration_status', 255)->nullable();
            $table->text('session_index', 255)->nullable();
        });
        Schema::create('mo_admin', function (Blueprint $table) {
            $table->string('id', 10)->unique()->nullable();
            $table->text('email', 255)->nullable();
            $table->text('password', 255)->nullable();
        });
        $tables = [
            'mo_config',
            'mo_admin'
        ];
        foreach ($tables as $table) {
            DB::statement('ALTER TABLE ' . $table . ' ENGINE = InnoDB');
        }
        $sp_base_url = str_replace("/create_tables", "", (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
        $sp_entity_id = $sp_base_url . '/miniorange_laravel_saml_connector';
        $acs_url = $sp_base_url . '/sso.php';
        DB::statement("INSERT INTO mo_config(id,mo_saml_host_name,mo_saml_free_version,sp_base_url,sp_entity_id,acs_url,mo_saml_new_registration) VALUES('1','https://login.xecurify.com/','".base64_encode(AESEncryption::encrypt_data('MA==', "M12K19FV"))."','".$sp_base_url."','".$sp_entity_id."','".$acs_url."','true')");
        DB::insert('insert into mo_admin (id) values (1)');
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mo_config');
        Schema::dropIfExists('mo_admin');
    }
}