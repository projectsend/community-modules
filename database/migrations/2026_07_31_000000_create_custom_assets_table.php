<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_assets', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('language');
            $table->text('content');
            $table->json('surfaces');
            $table->string('position');
            $table->boolean('enabled')->default(false);
            // Unsigned integer, not a foreign key: this package must not
            // assume the host's users table shape or name.
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_assets');
    }
};
