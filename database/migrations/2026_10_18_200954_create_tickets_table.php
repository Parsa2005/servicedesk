<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_code')->unique(); // کد رهگیری یکتا
            
            // 👈 شرکت مبدأ (برای مشتریان آزاد می‌تواند null باشد)
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            
            // شرکت و دپارتمان مقصد
            $table->foreignId('target_company_id')->nullable()->constrained('companies')->cascadeOnDelete(); 
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            
            $table->foreignId('category_id')->constrained('ticket_categories')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            // 👈 شخص مسئول در دپارتمان مقصد (برای گردش کار A -> B -> C)
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();            
            
            $table->string('subject');
            $table->text('description');
            
            $table->enum('priority', ['low', 'normal', 'high', 'critical'])->default('normal');
            $table->enum('status', ['open', 'pending', 'in_progress', 'resolved', 'closed'])->default('open');
            
            $table->timestamp('sla_due_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};