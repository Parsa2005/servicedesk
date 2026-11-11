<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_code')->unique(); 
            
        
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            
            
            $table->foreignId('target_company_id')->nullable()->constrained('companies')->cascadeOnDelete(); 
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            
            $table->foreignId('category_id')->constrained('ticket_categories')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
          
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