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
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('assignee_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()     // ユーザー削除時はNULLに
                  ->after('created_by');

            // よく検索するならインデックスも
            $table->index(['group_id', 'assignee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['group_id', 'assignee_id']);
            $table->dropConstrainedForeignId('assignee_id');
        });
    }
};
