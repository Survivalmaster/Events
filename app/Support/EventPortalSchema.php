<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EventPortalSchema
{
    public static function ensure(): void
    {
        static $ensured = false;

        if ($ensured) {
            return;
        }

        self::ensureEventsTable();
        self::ensureEnvironmentalEventsTable();
        self::ensureSettingsTable();

        $ensured = true;
    }

    private static function ensureEventsTable(): void
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

            return;
        }

        self::addColumnIfMissing('events', 'banner_url', fn (Blueprint $table) => $table->string('banner_url', 1000)->default(''));
        self::addColumnIfMissing('events', 'banner_pos_x', fn (Blueprint $table) => $table->unsignedTinyInteger('banner_pos_x')->default(50));
        self::addColumnIfMissing('events', 'banner_pos_y', fn (Blueprint $table) => $table->unsignedTinyInteger('banner_pos_y')->default(50));
        self::addColumnIfMissing('events', 'banner_zoom', fn (Blueprint $table) => $table->decimal('banner_zoom', 4, 2)->default(1));
    }

    private static function ensureEnvironmentalEventsTable(): void
    {
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
                $table->text('label')->nullable();
                $table->index('name');
                $table->index('weight');
            });

            return;
        }

        self::addColumnIfMissing('environmental_events', 'event_id', fn (Blueprint $table) => $table->string('event_id', 50)->default(''));
        self::addColumnIfMissing('environmental_events', 'faction_flags', fn (Blueprint $table) => $table->string('faction_flags')->default(''));
        self::addColumnIfMissing('environmental_events', 'weight', fn (Blueprint $table) => $table->unsignedTinyInteger('weight')->default(5));
        self::addColumnIfMissing('environmental_events', 'label', fn (Blueprint $table) => $table->text('label')->nullable());
        self::addColumnIfMissing('environmental_events', 'banner_url', fn (Blueprint $table) => $table->string('banner_url', 1000)->default(''));
        self::addColumnIfMissing('environmental_events', 'banner_pos_x', fn (Blueprint $table) => $table->unsignedTinyInteger('banner_pos_x')->default(50));
        self::addColumnIfMissing('environmental_events', 'banner_pos_y', fn (Blueprint $table) => $table->unsignedTinyInteger('banner_pos_y')->default(50));
        self::addColumnIfMissing('environmental_events', 'banner_zoom', fn (Blueprint $table) => $table->decimal('banner_zoom', 4, 2)->default(1));
    }

    private static function ensureSettingsTable(): void
    {
        if (Schema::hasTable('app_settings')) {
            return;
        }

        Schema::create('app_settings', function (Blueprint $table): void {
            $table->string('setting_key', 50)->primary();
            $table->text('setting_value');
            $table->timestamp('updated_at')->nullable();
        });
    }

    private static function addColumnIfMissing(string $tableName, string $columnName, callable $definition): void
    {
        if (Schema::hasColumn($tableName, $columnName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($definition): void {
            $definition($table);
        });
    }
}
