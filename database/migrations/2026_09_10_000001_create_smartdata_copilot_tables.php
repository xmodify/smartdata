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
        if (!Schema::hasTable('ai_settings')) {
            Schema::create('ai_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key_name', 100)->unique();
                $table->text('key_value')->nullable();
                $table->string('description', 255)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ai_chat_sessions')) {
            Schema::create('ai_chat_sessions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('session_uuid', 64)->unique();
                $table->string('title', 255)->default('การสนทนาใหม่');
                $table->string('target_db', 50)->default('hosxp');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ai_chat_messages')) {
            Schema::create('ai_chat_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('session_id')->index();
                $table->string('role', 20); // user, assistant, system
                $table->longText('content');
                $table->string('message_type', 30)->default('text'); // text, sql_query, rag_result
                $table->text('generated_sql')->nullable();
                $table->longText('query_result')->nullable();
                $table->string('target_db', 50)->nullable();
                $table->text('sources')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ai_knowledge_docs')) {
            Schema::create('ai_knowledge_docs', function (Blueprint $table) {
                $table->id();
                $table->string('title', 255);
                $table->string('category', 100)->default('ทั่วไป');
                $table->string('filename', 255);
                $table->string('file_path', 255);
                $table->unsignedBigInteger('file_size')->default(0);
                $table->string('file_type', 20);
                $table->string('status', 30)->default('unindexed'); // unindexed, indexing, indexed, failed
                $table->text('error_message')->nullable();
                $table->integer('chunks_count')->default(0);
                $table->string('embedded_model', 100)->nullable();
                $table->unsignedBigInteger('uploaded_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ai_knowledge_chunks')) {
            Schema::create('ai_knowledge_chunks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('doc_id')->index();
                $table->integer('chunk_index')->default(0);
                $table->longText('content');
                $table->longText('embedding')->nullable();
                $table->integer('embedding_dim')->nullable();
                $table->integer('page_number')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_knowledge_chunks');
        Schema::dropIfExists('ai_knowledge_docs');
        Schema::dropIfExists('ai_chat_messages');
        Schema::dropIfExists('ai_chat_sessions');
        Schema::dropIfExists('ai_settings');
    }
};
