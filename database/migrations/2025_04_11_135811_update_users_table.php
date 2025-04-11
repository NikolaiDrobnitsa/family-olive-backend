<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->string('verification_code')->nullable();
            $table->string('interest_type')->nullable(); // "Плантация 20 га" или "5 га + коттедж"
            $table->string('ip_address')->nullable();
            $table->text('utm_source')->nullable();
            $table->text('utm_medium')->nullable();
            $table->text('utm_campaign')->nullable();
            $table->text('utm_term')->nullable();
            $table->text('utm_content')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'is_admin', 'is_verified', 'verification_code',
                'interest_type', 'ip_address', 'utm_source', 'utm_medium',
                'utm_campaign', 'utm_term', 'utm_content'
            ]);
        });
    }
};
