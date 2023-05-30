<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_number')->nullable();
            $table->string('IBAN')->nullable();
            $table->enum('card_status', ['active', 'expired', 'disable', 'unassigned'])->nullable()->default('unassigned');
            $table->string('card_number')->nullable();
            $table->enum('cheque_book_status', ['active', 'expired', 'disable', 'unassigned'])->nullable()->default('unassigned');
            $table->string('cheque_book_number_from')->nullable();
            $table->string('cheque_book_number_to')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts');
    }
}
