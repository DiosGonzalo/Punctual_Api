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
        Schema::create('jefes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellidos');
            $table->string('email')->unique();
            $table->string('telefono');
            $table->foreignId('id_horario')->nullable()->constrained('horarios')->onDelete('set null');
            $table->foreignId('id_workplace')->nullable()->constrained('workplaces')->onDelete('set null');
            $table->foreignId('id_modalidad')->nullable()->constrained('modalidades')->onDelete('set null');
            $table->boolean('is_presente')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jefes');
    }
};
