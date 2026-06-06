<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('absence_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('absence_type_id')->constrained('absence_types');

            $table->date('start_date');
            $table->date('end_date');

            $table->string('status');
            $table->text('comments')->nullable();

            $table->timestamps();

            // Acelera el filtrado de solicitudes por empleado y estado (pending/approved/rejected)
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absence_requests');
    }
};
