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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debt_id')->constrained()->onDelete('cascade'); // Référence à la dette
            $table->decimal('amount', 10, 2); // Montant du paiement
            $table->dateTime('payment_date'); // Date du paiement
            $table->string('payment_method'); // Méthode de paiement
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
