<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            
            // Standard accounting hierarchies
            $table->string('code')->index(); // e.g. 1001, 2000
            $table->string('name');
            
            // Type Enum: Asset, Liability, Equity, Income, Expense
            $table->string('type'); 
            
            // Sub-type for more detailed reporting (e.g. Current Asset, Non-current Asset)
            $table->string('sub_type')->nullable(); 

            // Parent account for hierarchy (Self-referencing)
            $table->foreignId('parent_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            
            // Normal Balance (Debit/Credit) to help with validation
            // Asset/Expense = Debit, Liability/Equity/Income = Credit
            $table->enum('normal_balance', ['debit', 'credit']);
            
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Unique code per company
            $table->unique(['company_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
