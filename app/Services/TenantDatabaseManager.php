<?php

namespace App\Services;

use App\Models\Central\School;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantDatabaseManager
{
    /**
     * The current school instance.
     *
     * @var School|null
     */
    protected static $currentSchool = null;

    /**
     * Identify and set the tenant from the request.
     *
     * @param string|null $identifier Slug, domain, or school ID
     * @return School|null
     */
    public function identifyTenant(?string $identifier): ?School
    {
        if (!$identifier) {
            return null;
        }

        // Try to find school by slug or domain
        $school = School::where('slug', $identifier)
            ->orWhere('domain', $identifier)
            ->first();

        if ($school && $school->is_active) {
            static::$currentSchool = $school;
            return $school;
        }

        return null;
    }

    /**
     * Switch the database connection to a specific school.
     *
     * @param School $school
     * @return void
     */
    public function switchToSchool(School $school): void
    {
        Config::set('database.connections.tenant.database', $school->database_name);
        DB::purge('tenant');
        DB::reconnect('tenant');
        
        static::$currentSchool = $school;
        
        // Store in app container for easy access
        app()->instance('current_school', $school);
    }

    /**
     * Switch to central database.
     *
     * @return void
     */
    public function switchToCentral(): void
    {
        Config::set('database.default', 'central');
        DB::purge('central');
        DB::reconnect('central');
        
        static::$currentSchool = null;
    }

    /**
     * Get the current school.
     *
     * @return School|null
     */
    public function getCurrentSchool(): ?School
    {
        return static::$currentSchool ?? app('current_school', null);
    }

    /**
     * Check if a tenant is currently set.
     *
     * @return bool
     */
    public function hasTenant(): bool
    {
        return static::$currentSchool !== null || app()->has('current_school');
    }

    /**
     * Execute a callback using a specific school's database.
     *
     * @param School $school
     * @param callable $callback
     * @return mixed
     */
    public function executeForSchool(School $school, callable $callback)
    {
        $previousSchool = static::$currentSchool;
        
        $this->switchToSchool($school);
        
        try {
            return $callback();
        } finally {
            if ($previousSchool) {
                $this->switchToSchool($previousSchool);
            } else {
                $this->switchToCentral();
            }
        }
    }

    /**
     * Create a new database for a school.
     *
     * @param string $databaseName
     * @return bool
     */
    public function createDatabase(string $databaseName): bool
    {
        try {
            DB::connection('central')->statement("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to create database {$databaseName}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Drop a school's database.
     *
     * @param string $databaseName
     * @return bool
     */
    public function dropDatabase(string $databaseName): bool
    {
        try {
            DB::connection('central')->statement("DROP DATABASE IF EXISTS `{$databaseName}`");
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to drop database {$databaseName}: " . $e->getMessage());
            return false;
        }
    }
}
