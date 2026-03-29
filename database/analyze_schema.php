<?php

/**
 * Database Schema Analysis Tool
 * 
 * This script analyzes all migration files and generates a comprehensive
 * database schema document that can be compared with the data dictionary.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$migrationsPath = __DIR__ . '/migrations';
$migrations = glob($migrationsPath . '/*.php');

$schema = [];
$tables = [];

// Parse all migration files
foreach ($migrations as $migrationFile) {
    $content = file_get_contents($migrationFile);
    
    // Extract table creation statements
    if (preg_match_all('/Schema::create\([\'"]([^\'"]+)[\'"]/', $content, $matches)) {
        foreach ($matches[1] as $tableName) {
            if (!isset($tables[$tableName])) {
                $tables[$tableName] = [
                    'name' => $tableName,
                    'columns' => [],
                    'indexes' => [],
                    'foreign_keys' => [],
                    'migration_file' => basename($migrationFile)
                ];
            }
            
            // Extract columns
            if (preg_match_all('/\$table->(id|string|text|integer|bigInteger|tinyInteger|boolean|enum|date|dateTime|timestamp|foreignId|morphs)\([\'"]([^\'"]*)[\'"](?:[^)]*)\)/', $content, $colMatches, PREG_SET_ORDER)) {
                foreach ($colMatches as $colMatch) {
                    $type = $colMatch[1];
                    $name = $colMatch[2];
                    
                    if ($name && $name !== 'id') {
                        $tables[$tableName]['columns'][$name] = [
                            'type' => $type,
                            'nullable' => strpos($colMatch[0], 'nullable') !== false,
                            'default' => null,
                            'unique' => strpos($colMatch[0], 'unique') !== false
                        ];
                    }
                }
            }
            
            // Extract foreign keys
            if (preg_match_all('/foreignId\([\'"]([^\'"]+)[\'"]\)->constrained/', $content, $fkMatches)) {
                foreach ($fkMatches[1] as $fkColumn) {
                    $tables[$tableName]['foreign_keys'][] = $fkColumn;
                }
            }
        }
    }
    
    // Extract table modifications
    if (preg_match_all('/Schema::table\([\'"]([^\'"]+)[\'"]/', $content, $matches)) {
        foreach ($matches[1] as $tableName) {
            if (!isset($tables[$tableName])) {
                $tables[$tableName] = [
                    'name' => $tableName,
                    'columns' => [],
                    'indexes' => [],
                    'foreign_keys' => [],
                    'migration_file' => basename($migrationFile)
                ];
            }
            
            // Extract added columns
            if (preg_match_all('/\$table->(string|text|integer|bigInteger|tinyInteger|boolean|enum|date|dateTime|timestamp|foreignId)\([\'"]([^\'"]*)[\'"](?:[^)]*)\)/', $content, $colMatches, PREG_SET_ORDER)) {
                foreach ($colMatches as $colMatch) {
                    $type = $colMatch[1];
                    $name = $colMatch[2];
                    
                    if ($name) {
                        $tables[$tableName]['columns'][$name] = [
                            'type' => $type,
                            'nullable' => strpos($colMatch[0], 'nullable') !== false,
                            'default' => null,
                            'unique' => strpos($colMatch[0], 'unique') !== false
                        ];
                    }
                }
            }
        }
    }
}

// Sort tables alphabetically
ksort($tables);

// Generate markdown document
$output = "# OSA Hub Database Schema Analysis\n\n";
$output .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
$output .= "This document lists all database tables and their structure as defined in migration files.\n\n";
$output .= "## Table of Contents\n\n";

foreach ($tables as $tableName => $tableInfo) {
    $anchor = str_replace('_', '-', $tableName);
    $output .= "- [$tableName](#$anchor)\n";
}

$output .= "\n---\n\n";

// Generate detailed table information
foreach ($tables as $tableName => $tableInfo) {
    $output .= "## $tableName\n\n";
    $output .= "**Migration File:** `{$tableInfo['migration_file']}`\n\n";
    
    if (!empty($tableInfo['columns'])) {
        $output .= "### Columns\n\n";
        $output .= "| Column Name | Type | Nullable | Unique | Description |\n";
        $output .= "|------------|------|----------|--------|-------------|\n";
        
        foreach ($tableInfo['columns'] as $colName => $colInfo) {
            $nullable = $colInfo['nullable'] ? 'Yes' : 'No';
            $unique = $colInfo['unique'] ? 'Yes' : 'No';
            $output .= "| `$colName` | {$colInfo['type']} | $nullable | $unique | |\n";
        }
        $output .= "\n";
    }
    
    if (!empty($tableInfo['foreign_keys'])) {
        $output .= "### Foreign Keys\n\n";
        foreach ($tableInfo['foreign_keys'] as $fk) {
            $output .= "- `$fk`\n";
        }
        $output .= "\n";
    }
    
    $output .= "---\n\n";
}

// Save to file
file_put_contents(__DIR__ . '/SCHEMA_ANALYSIS.md', $output);

echo "Schema analysis complete! Generated: database/SCHEMA_ANALYSIS.md\n";
echo "Total tables found: " . count($tables) . "\n";

// Also output summary
echo "\n=== TABLE SUMMARY ===\n";
foreach ($tables as $tableName => $tableInfo) {
    $colCount = count($tableInfo['columns']);
    echo sprintf("%-40s %3d columns\n", $tableName, $colCount);
}

