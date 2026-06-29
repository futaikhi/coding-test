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
            $table->boolean('is_active')->default(true); // Syarat: BOOLEAN
            $table->json('attributes')->nullable();       // Syarat: JSON (Contoh: {"rack": "A1", "temperature": "cool"})
            $table->timestamps();
            $table->softDeletes();                        // Syarat: SOFT DELETES
        });

        // 2. TABEL MATERIAL (Memenuhi unsur: UUID, DateTime, File Attachment, Relasi ke Kategori)
        Schema::create('materials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->constrained('categories')->onDelete('cascade'); // RELASI MANY-TO-ONE
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('document_path')->nullable(); // Tempat simpan path file PDF
            $table->date('published_at');            // Syarat: DATETIME
            $table->timestamps();
            $table->softDeletes();                        // Syarat: SOFT DELETES
        });

        // 3. TABEL MUTASI STOK (Memenuhi unsur: UUID, Relasi ke Material, Pencatatan Transaksional)
        Schema::create('stock_mutations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('material_id')->constrained('materials')->onDelete('cascade'); // RELASI MANY-TO-ONE
            $table->enum('type', ['in', 'out']);
            $table->integer('quantity');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();                        // Syarat: SOFT DELETES
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_mutations');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('categories');
    }
};