<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->enum('priority', ['low', 'normal', 'high', 'critical']);
            $table->unsignedInteger('hours'); 
            $table->timestamps();

            $table->unique(['company_id', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slas');
    }
};