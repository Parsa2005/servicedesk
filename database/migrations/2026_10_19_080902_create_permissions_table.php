<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // نام فارسی دسترسی (مثلاً: ایجاد تیکت)
            $table->string('slug')->unique(); // شناسه لاتین (مثلاً: ticket.create)
            $table->string('display_name')->nullable();
            $table->string('group')->nullable();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('permissions')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};