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
    // 1. Rename the main table
    Schema::rename('companies', 'profiles');

    // 2. Add new columns to profiles
    Schema::table('profiles', function (Blueprint $table) {
      $table->enum('type', ['business', 'personal'])->default('business')->after('name');
    });

    // 3. Update Foreign Keys and Column Names for related tables

    // --- fiscal_years ---
    Schema::table('fiscal_years', function (Blueprint $table) {
      // Drop old constraint: fiscal_years_company_id_foreign
      $table->dropForeign(['company_id']);
      $table->renameColumn('company_id', 'profile_id');
    });

    Schema::table('fiscal_years', function (Blueprint $table) {
      $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('cascade');
    });

    // --- chart_of_accounts ---
    Schema::table('chart_of_accounts', function (Blueprint $table) {
      // Drop old constraint: chart_of_accounts_company_id_foreign
      $table->dropForeign(['company_id']);
      $table->renameColumn('company_id', 'profile_id');
    });

    Schema::table('chart_of_accounts', function (Blueprint $table) {
      $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('cascade');
    });

    // --- journal_entries ---
    Schema::table('journal_entries', function (Blueprint $table) {
      // Drop old constraint: journal_entries_company_id_foreign
      $table->dropForeign(['company_id']);
      $table->renameColumn('company_id', 'profile_id');
    });

    Schema::table('journal_entries', function (Blueprint $table) {
      $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('cascade');
    });

    // 4. Create Pivot Table for User-Profile relationship
    Schema::create('profile_user', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
      $table->string('role')->default('owner'); // e.g., owner, editor, viewer
      $table->timestamps();

      // Prevent duplicate access entries
      $table->unique(['user_id', 'profile_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    // Reverse Pivot
    Schema::dropIfExists('profile_user');

    // Reverse Journal Entries
    Schema::table('journal_entries', function (Blueprint $table) {
      $table->dropForeign(['profile_id']);
      $table->renameColumn('profile_id', 'company_id');
    });
    Schema::table('journal_entries', function (Blueprint $table) {
      $table->foreign('company_id')->references('id')->on('profiles')->onDelete('cascade'); // Point back to profiles as companies
    });

    // Reverse Chart of Accounts
    Schema::table('chart_of_accounts', function (Blueprint $table) {
      $table->dropForeign(['profile_id']);
      $table->renameColumn('profile_id', 'company_id');
    });
    Schema::table('chart_of_accounts', function (Blueprint $table) {
      $table->foreign('company_id')->references('id')->on('profiles')->onDelete('cascade');
    });

    // Reverse Fiscal Years
    Schema::table('fiscal_years', function (Blueprint $table) {
      $table->dropForeign(['profile_id']);
      $table->renameColumn('profile_id', 'company_id');
    });
    Schema::table('fiscal_years', function (Blueprint $table) {
      $table->foreign('company_id')->references('id')->on('profiles')->onDelete('cascade');
    });

    // Reverse Profiles Table
    Schema::table('profiles', function (Blueprint $table) {
      $table->dropColumn('type');
    });

    Schema::rename('profiles', 'companies');
  }
};
