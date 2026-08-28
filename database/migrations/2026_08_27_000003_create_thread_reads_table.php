<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thread_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained()->cascadeOnDelete();
            // reader_user_id null => lecture "anonyme" (expéditeur via code), sinon membre
            $table->foreignId('reader_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('last_read_at');
            $table->timestamps();

            $table->unique(['thread_id', 'reader_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thread_reads');
    }
};
