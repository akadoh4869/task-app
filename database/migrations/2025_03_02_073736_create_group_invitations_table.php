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
        Schema::create('group_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->onDelete('cascade'); // グループID
            $table->foreignId('inviter_id')->constrained('users')->onDelete('cascade'); // 招待したユーザー
            $table->foreignId('invitee_id')->constrained('users')->onDelete('cascade'); // 招待されたユーザー
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending'); // 招待ステータス
            // 同じユーザーが同じグループに重複して招待されないように制約を追加
            $table->unique(['group_id', 'invitee_id']);
            $table->timestamp('responded_at')->nullable(); // ← これが必要！
            $table->timestamps();
        });
         
        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_invitations');
    }
};
