<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. TABEL MASTER KATEGORI (Memenuhi unsur: UUID, Boolean, JSON, SoftDeletes)
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->boolean('is_active')->default(true); 
            $table->json('attributes')->nullable();       
            $table->timestamps();
            $table->softDeletes();                       
        });

        // 2. TABEL MATERIAL (Memenuhi unsur: UUID, DateTime, File Attachment, Relasi ke Kategori)
        Schema::create('materials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('document_path')->nullable(); 
            $table->date('published_at');            
            $table->timestamps();
            $table->softDeletes();                       
        });

        // 3. TABEL MUTASI STOK (Memenuhi unsur: UUID, Relasi ke Material, Pencatatan Transaksional)
        Schema::create('stock_mutations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('material_id')->constrained('materials')->onDelete('cascade');
            $table->enum('type', ['in', 'out']);
            $table->integer('quantity');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();                        
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_mutations');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('categories');
    }
};