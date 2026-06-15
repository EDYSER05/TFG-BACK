<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('company_holiday', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('holiday_id')->constrained('holidays')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'holiday_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_holiday');
    }
};
