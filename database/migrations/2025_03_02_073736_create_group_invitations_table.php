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
            $table->foreignId('invited_by')->constrained('users')->onDelete('cascade'); // 招待したユーザー
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // 招待されたユーザー
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending'); // 招待ステータス
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
