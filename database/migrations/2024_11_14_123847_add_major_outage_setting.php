<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        rescue(fn () => $this->migrator->add('app.major_outage_threshold', 25));
        rescue(fn () => $this->migrator->add('theme.primary'));
        rescue(fn () => $this->migrator->add('theme.secondary'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
