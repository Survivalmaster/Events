<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table): void {
                $table->id();
                $table->timestamps();
                $table->string('status', 20)->default('NEW');
                $table->string('handler', 100)->default('');
                $table->string('type', 20)->default('');
                $table->date('event_date');
                $table->time('event_time');
                $table->string('name');
                $table->string('district')->default('');
                $table->string('discord', 500)->default('');
                $table->string('banner_url', 1000)->default('');
                $table->unsignedTinyInteger('banner_pos_x')->default(50);
                $table->unsignedTinyInteger('banner_pos_y')->default(50);
                $table->decimal('banner_zoom', 4, 2)->default(1);
                $table->text('description');
                $table->string('property_id', 50);
                $table->text('notes');
                $table->index('event_date');
                $table->index('status');
            });
        }

        if (! Schema::hasTable('environmental_events')) {
            Schema::create('environmental_events', function (Blueprint $table): void {
                $table->id();
                $table->timestamps();
                $table->string('event_id', 50)->default('');
                $table->unsignedTinyInteger('weight')->default(5);
                $table->string('faction_flags')->default('');
                $table->string('type', 50)->default('');
                $table->string('name');
                $table->string('district')->default('');
                $table->string('banner_url', 1000)->default('');
                $table->unsignedTinyInteger('banner_pos_x')->default(50);
                $table->unsignedTinyInteger('banner_pos_y')->default(50);
                $table->decimal('banner_zoom', 4, 2)->default(1);
                $table->text('label');
                $table->index('name');
                $table->index('weight');
            });
        }

        if (! Schema::hasTable('app_settings')) {
            Schema::create('app_settings', function (Blueprint $table): void {
                $table->string('setting_key', 50)->primary();
                $table->text('setting_value');
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
        Schema::dropIfExists('environmental_events');
        Schema::dropIfExists('events');
    }
};
