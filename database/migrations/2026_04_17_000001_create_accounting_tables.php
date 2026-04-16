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
        // Chart of Accounts - The main ledger accounts
        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->enum('sub_type', [
                // Assets
                'current_asset', 'fixed_asset', 'inventory', 'bank', 'accounts_receivable', 'cash',
                // Liabilities
                'current_liability', 'long_term_liability', 'accounts_payable',
                // Equity
                'capital', 'retained_earnings', 'drawings',
                // Revenue
                'sales', 'other_income', 'discount_received',
                // Expense
                'cost_of_goods_sold', 'operating_expense', 'discount_allowed', 'other_expense'
            ])->nullable();
            $table->foreignId('parent_id')->nullable()->references('id')->on('ledger_accounts')->nullOnDelete();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['team_id', 'type']);
            $table->unique(['team_id', 'code']);
        });

        // Journal Entries - The main accounting entries
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('entry_number');
            $table->date('entry_date');
            $table->string('reference_type')->nullable(); // Model class e.g., Invoice::class
            $table->unsignedBigInteger('reference_id')->nullable(); // Model ID
            $table->string('description')->nullable();
            $table->enum('status', ['draft', 'posted', 'voided'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['team_id', 'entry_date']);
            $table->index(['reference_type', 'reference_id']);
            $table->unique(['team_id', 'entry_number']);
        });

        // Journal Entry Lines - Individual debit/credit lines
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ledger_account_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['debit', 'credit']);
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['journal_entry_id', 'type']);
            $table->index('ledger_account_id');
        });

        // Account Transactions - Track all transactions per account
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ledger_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('journal_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('journal_entry_line_id')->constrained()->cascadeOnDelete();
            $table->date('transaction_date');
            $table->enum('type', ['debit', 'credit']);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2); // Running balance after this transaction
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'ledger_account_id', 'transaction_date']);
            $table->index(['reference_type', 'reference_id']);
        });

        // Fiscal Years
        Schema::create('fiscal_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_closed')->default(false);
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'start_date', 'end_date']);
        });

        // Budget Tracking
        Schema::create('account_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fiscal_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ledger_account_id')->constrained()->cascadeOnDelete();
            $table->decimal('budgeted_amount', 15, 2);
            $table->decimal('used_amount', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['team_id', 'fiscal_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_budgets');
        Schema::dropIfExists('fiscal_years');
        Schema::dropIfExists('account_transactions');
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('ledger_accounts');
    }
};
