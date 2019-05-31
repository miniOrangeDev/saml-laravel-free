<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->string('mo_saml_host_name', 255)->nullable();
            $table->string('mo_saml_admin_email', 255)->nullable();
            $table->string('mo_saml_admin_password', 255)->nullable();
            $table->string('mo_saml_admin_customer_key', 255)->nullable();
            $table->string('mo_saml_admin_api_key', 255)->nullable();
            $table->string('mo_saml_customer_token', 255)->nullable();
            $table->string('mo_saml_free_version', 255)->nullable();
            $table->string('mo_saml_message', 300)->nullable();
            $table->string('saml_identity_name', 255)->nullable();
            $table->string('idp_entity_id', 255)->nullable();
            $table->string('saml_login_url', 255)->nullable();
            $table->string('saml_login_binding_type', 255)->nullable();
            $table->string('sp_base_url', 255)->nullable();
            $table->string('sp_entity_id', 255)->nullable();
            $table->string('acs_url', 255)->nullable();
            $table->string('single_logout_url', 255)->nullable();
            $table->string('saml_am_email', 255)->nullable();
            $table->string('saml_am_username', 255)->nullable();
            $table->string('relaystate_url', 255)->nullable();
            $table->string('site_logout_url', 255)->nullable();
            $table->string('saml_x509_certificate', 1500)->nullable();
            $table->string('mo_saml_new_registration', 10)->nullable();
            $table->string('mo_saml_admin_phone', 20)->nullable();
            $table->string('mo_saml_verify_customer', 10)->nullable();
            $table->string('mo_saml_idp_config_complete', 255)->nullable();
            $table->string('mo_saml_transactionId', 255)->nullable();
            $table->string('mo_saml_guest_enabled', 10)->nullable();
            $table->string('mo_saml_registration_status', 255)->nullable();
            $table->string('session_index', 255)->nullable();
        });
        Schema::create('mo_admin', function (Blueprint $table) {
            $table->string('id', 10)->unique()->nullable();
            $table->string('email', 255)->nullable();
            $table->string('password', 255)->nullable();
        });
        $tables = [
            'mo_config',
            'mo_admin'
        ];
        foreach ($tables as $table) {
            DB::statement('ALTER TABLE ' . $table . ' ENGINE = InnoDB');
        }
        DB::statement("INSERT INTO mo_config(id,mo_saml_host_name,mo_saml_new_registration) VALUES('1','https://auth.miniorange.com/','true')");
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