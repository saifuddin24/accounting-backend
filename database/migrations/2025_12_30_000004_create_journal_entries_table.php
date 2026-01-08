<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fiscal_year_id')->constrained();
            
            $table->string('entry_number')->index(); // e.g. JE-2025-0001
            $table->date('date');
            $table->text('description')->nullable();
            
            // Reference to external documents (e.g. Invoice #123)
            $table->string('reference')->nullable();
            
            $table->decimal('total_amount', 20, 2)->default(0);

            // Status: draft, posted, cancelled
            $table->string('status')->default('draft');
            
            $table->timestamps();

            $table->unique(['company_id', 'entry_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
