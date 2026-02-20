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
    Schema::create('contacts', function (Blueprint $table) {
      $table->id();
      $table->foreignId('profile_id')->constrained('profiles')->onDelete('cascade');
      $table->string('name');
      $table->enum('type', ['customer', 'vendor', 'employee', 'other'])->default('other');
      $table->string('email')->nullable();
      $table->string('phone')->nullable();
      $table->text('address')->nullable();
      $table->text('notes')->nullable();
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('contacts');
  }
};
