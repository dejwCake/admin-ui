<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wysiwyg_media', function (Blueprint $table) {
            $table->increments('id');
            $table->string('file_path');
            $table->unsignedInteger('wysiwygable_id')->nullable()->index();
            $table->string('wysiwygable_type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wysiwyg_media');
    }
};
