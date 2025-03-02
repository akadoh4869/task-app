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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('group_name'); // グループ名
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade'); // グループ作成者（オーナー）
            $table->text('description')->nullable(); // グループの説明
            $table->boolean('invite_only')->default(true); // 招待制グループかどうか（デフォルト: true）
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
