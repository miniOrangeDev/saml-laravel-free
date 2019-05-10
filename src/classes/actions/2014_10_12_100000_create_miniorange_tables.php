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
            $table->string('mo_saml_host_name', 100)->nullable();
            $table->string('mo_saml_admin_email', 100)->nullable();
            $table->string('mo_saml_admin_password', 100)->nullable();
            $table->string('mo_saml_admin_customer_key', 100)->nullable();
            $table->string('mo_saml_admin_api_key', 100)->nullable();
            $table->string('mo_saml_customer_token', 100)->nullable();
            $table->string('mo_saml_free_version', 100)->nullable();
            $table->string('mo_saml_message', 300)->nullable();
            $table->string('saml_identity_name', 100)->nullable();
            $table->string('idp_entity_id', 100)->nullable();
            $table->string('saml_login_url', 100)->nullable();
            $table->string('saml_login_binding_type', 100)->nullable();
            $table->string('sp_base_url', 100)->nullable();
            $table->string('sp_entity_id', 100)->nullable();
            $table->string('acs_url', 100)->nullable();
            $table->string('single_logout_url', 100)->nullable();
            $table->string('saml_am_email', 100)->nullable();
            $table->string('saml_am_username', 100)->nullable();
            $table->string('relaystate_url', 100)->nullable();
            $table->string('site_logout_url', 100)->nullable();
            $table->string('saml_x509_certificate', 1500)->nullable();
            $table->string('mo_saml_new_registration', 10)->nullable();
            $table->string('mo_saml_admin_phone', 20)->nullable();
            $table->string('mo_saml_verify_customer', 10)->nullable();
            $table->string('mo_saml_idp_config_complete', 100)->nullable();
            $table->string('mo_saml_transactionId', 100)->nullable();
            $table->string('mo_saml_guest_enabled',10)->nullable();
            $table->string('mo_saml_registration_status', 100)->nullable();
            $table->string('session_index', 100)->nullable();
        });
        Schema::create('mo_admin', function (Blueprint $table) {
            $table->string('id', 10);
            $table->string('email', 100);
            $table->string('password', 100);
        });
        $tables = [
            'mo_config',
            'mo_admin'
        ];
        foreach ($tables as $table) {
            DB::statement('ALTER TABLE ' . $table . ' ENGINE = InnoDB');
        }
        DB::statement("INSERT INTO mo_config(id,mo_saml_host_name,mo_saml_new_registration) VALUES('1','https://auth.miniorange.com/','true')");
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