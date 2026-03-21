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
        Schema::create('invoice_counter', function (Blueprint $table) {
            $table->id();
            $table->integer('counter')->default(0);
            $table->timestamp('updated_at')->nullable();
            // No created_at — InvoiceCounter model uses $timestamps = false
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_counter');
    }
};
