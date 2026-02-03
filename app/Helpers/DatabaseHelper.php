<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class DatabaseHelper
{
    /**
     * Get SQL for extracting year from a date column.
     */
    public static function year(string $column): string
    {
        $driver = DB::getDriverName();

        return match ($driver) {
            'sqlite' => "strftime('%Y', {$column})",
            default => "YEAR({$column})",
        };
    }

    /**
     * Get SQL for extracting month from a date column.
     */
    public static function month(string $column): string
    {
        $driver = DB::getDriverName();

        return match ($driver) {
            'sqlite' => "CAST(strftime('%m', {$column}) AS INTEGER)",
            default => "MONTH({$column})",
        };
    }

    /**
     * Get SQL for DISTINCT year selection.
     */
    public static function distinctYear(string $column): string
    {
        $driver = DB::getDriverName();

        return match ($driver) {
            'sqlite' => "DISTINCT strftime('%Y', {$column}) as year",
            default => "DISTINCT YEAR({$column}) as year",
        };
    }

    /**
     * Get SQL for DISTINCT year selection as integer.
     */
    public static function distinctYearInt(string $column): string
    {
        $driver = DB::getDriverName();

        return match ($driver) {
            'sqlite' => "DISTINCT CAST(strftime('%Y', {$column}) AS INTEGER) as year",
            default => "DISTINCT YEAR({$column}) as year",
        };
    }
}
