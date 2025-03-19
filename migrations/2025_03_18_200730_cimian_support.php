<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CimianSupport extends Migration
{
    public function up()
    {
        Schema::table('managedinstalls', function (Blueprint $table) {
            $table->string('client_type')->default('munki');
            $table->string('install_source')->nullable();
            $table->dateTime('install_date')->nullable();
        });
    }

    public function down()
    {
        Schema::table('managedinstalls', function (Blueprint $table) {
            $table->dropColumn(['client_type', 'install_source', 'install_date']);
        });
    }
}
