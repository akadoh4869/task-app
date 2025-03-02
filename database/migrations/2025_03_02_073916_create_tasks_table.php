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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->nullable()->constrained()->onDelete('cascade'); // グループタスクならグループID
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // タスクを作成した人
            $table->string('task_name'); // タスク名
            $table->text('description')->nullable(); // タスク詳細
            $table->timestamp('due_date')->nullable(); // 期日
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started'); // 進捗状況
            $table->foreignId('deleted_by')->nullable()->constrained('users'); // 誰が削除（完了）したのか
            $table->softDeletes(); // ソフトデリート（復元可能にする）
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
