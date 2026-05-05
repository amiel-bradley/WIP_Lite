<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->foreignId('parent_assignment_id')->nullable()->constrained('assignments')->onDelete('cascade')->after('id');
            $table->enum('assignment_type', ['cp', 'supervisor', 'tc'])->after('position_id');
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign(['parent_assignment_id']);
            $table->dropColumn(['parent_assignment_id', 'assignment_type']);
        });
    }
};
