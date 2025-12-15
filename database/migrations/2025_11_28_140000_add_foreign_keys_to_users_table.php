<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('id_rol')->references('id')->on('roles')->onDelete('set null');
            $table->foreign('id_horario')->references('id')->on('horarios')->onDelete('set null');
            $table->foreign('id_workplace')->references('id')->on('workplaces')->onDelete('set null');
            $table->foreign('id_modalidad')->references('id')->on('modalidades')->onDelete('set null');
            $table->foreign('id_departamento')->references('id')->on('departamentos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_rol']);
            $table->dropForeign(['id_horario']);
            $table->dropForeign(['id_workplace']);
            $table->dropForeign(['id_modalidad']);
            $table->dropForeign(['id_departamento']);
        });
    }
};
