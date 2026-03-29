<?php

/**
 * Data Dictionary Verification Tool
 * 
 * This script extracts the complete database schema from migrations
 * and generates a detailed report that can be compared with the data dictionary.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$migrationsPath = __DIR__ . '/migrations';
$migrations = glob($migrationsPath . '/*.php');
sort($migrations);

$schema = [];
$tables = [];
$relationships = [];

// Parse all migration files
foreach ($migrations as $migrationFile) {
    $content = file_get_contents($migrationFile);
    $basename = basename($migrationFile);
    
    // Extract table creation statements
    if (preg_match_all('/Schema::create\([\'"]([^\'"]+)[\'"]/', $content, $matches)) {
        foreach ($matches[1] as $tableName) {
            if (!isset($tables[$tableName])) {
                $tables[$tableName] = [
                    'name' => $tableName,
                    'columns' => [],
                    'indexes' => [],
                    'foreign_keys' => [],
                    'unique_constraints' => [],
                    'migrations' => []
                ];
            }
            
            $tables[$tableName]['migrations'][] = $basename;
            
            // Extract columns with full details
            $columnPattern = '/\$table->(id|string|text|integer|bigInteger|tinyInteger|smallInteger|boolean|enum|date|dateTime|timestamp|time|foreignId|morphs|json|decimal|float|double|unsignedInteger|unsignedBigInteger)\([\'"]([^\'"]*)[\'"](?:[^)]*)\)/';
            if (preg_match_all($columnPattern, $content, $colMatches, PREG_SET_ORDER)) {
                foreach ($colMatches as $colMatch) {
                    $type = $colMatch[1];
                    $name = $colMatch[2];
                    
                    if ($name && $name !== 'id') {
                        $fullMatch = $colMatch[0];
                        $nullable = strpos($fullMatch, 'nullable') !== false;
                        $unique = strpos($fullMatch, 'unique') !== false;
                        $default = null;
                        
                        // Extract default value
                        if (preg_match("/default\(([^)]+)\)/", $fullMatch, $defaultMatch)) {
                            $default = $defaultMatch[1];
                        }
                        
                        // Extract enum values
                        $enumValues = null;
                        if ($type === 'enum' && preg_match("/enum\([\'\"]([^\'\"]+)[\'\"],\s*\[([^\]]+)\]/", $fullMatch, $enumMatch)) {
                            $enumValues = array_map(function($v) { return trim($v, " '\""); }, explode(',', $enumMatch[2]));
                        }
                        
                        $tables[$tableName]['columns'][$name] = [
                            'type' => $type,
                            'nullable' => $nullable,
                            'default' => $default,
                            'unique' => $unique,
                            'enum_values' => $enumValues
                        ];
                    }
                }
            }
            
            // Extract foreign keys with details
            if (preg_match_all('/foreignId\([\'"]([^\'"]+)[\'"]\)->constrained\([\'"]([^\'"]*)[\'"](?:[^)]*)\)/', $content, $fkMatches, PREG_SET_ORDER)) {
                foreach ($fkMatches as $fkMatch) {
                    $column = $fkMatch[1];
                    $referencedTable = $fkMatch[2] ?: $column; // If no table specified, assume same name
                    $onDelete = 'restrict';
                    if (preg_match("/onDelete\(['\"]([^'\"]+)['\"]\)/", $fkMatch[0], $onDeleteMatch)) {
                        $onDelete = $onDeleteMatch[1];
                    }
                    
                    $tables[$tableName]['foreign_keys'][] = [
                        'column' => $column,
                        'references' => $referencedTable,
                        'on_delete' => $onDelete
                    ];
                }
            }
            
            // Extract unique constraints
            if (preg_match_all('/unique\([\'"]([^\'"]+)[\'"]\)/', $content, $uniqueMatches)) {
                foreach ($uniqueMatches[1] as $uniqueCol) {
                    if (!in_array($uniqueCol, $tables[$tableName]['unique_constraints'])) {
                        $tables[$tableName]['unique_constraints'][] = $uniqueCol;
                    }
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
                    'unique_constraints' => [],
                    'migrations' => []
                ];
            }
            
            if (!in_array($basename, $tables[$tableName]['migrations'])) {
                $tables[$tableName]['migrations'][] = $basename;
            }
            
            // Extract added columns
            $columnPattern = '/\$table->(string|text|integer|bigInteger|tinyInteger|smallInteger|boolean|enum|date|dateTime|timestamp|time|foreignId|json|decimal|float|double|unsignedInteger|unsignedBigInteger)\([\'"]([^\'"]*)[\'"](?:[^)]*)\)/';
            if (preg_match_all($columnPattern, $content, $colMatches, PREG_SET_ORDER)) {
                foreach ($colMatches as $colMatch) {
                    $type = $colMatch[1];
                    $name = $colMatch[2];
                    
                    if ($name) {
                        $fullMatch = $colMatch[0];
                        $nullable = strpos($fullMatch, 'nullable') !== false;
                        $unique = strpos($fullMatch, 'unique') !== false;
                        $default = null;
                        
                        if (preg_match("/default\(([^)]+)\)/", $fullMatch, $defaultMatch)) {
                            $default = $defaultMatch[1];
                        }
                        
                        $enumValues = null;
                        if ($type === 'enum' && preg_match("/enum\([\'\"]([^\'\"]+)[\'\"],\s*\[([^\]]+)\]/", $fullMatch, $enumMatch)) {
                            $enumValues = array_map(function($v) { return trim($v, " '\""); }, explode(',', $enumMatch[2]));
                        }
                        
                        $tables[$tableName]['columns'][$name] = [
                            'type' => $type,
                            'nullable' => $nullable,
                            'default' => $default,
                            'unique' => $unique,
                            'enum_values' => $enumValues
                        ];
                    }
                }
            }
        }
    }
}

// Sort tables alphabetically
ksort($tables);

// Generate comprehensive markdown document
$output = "# OSA Hub Database Schema - Complete Reference\n\n";
$output .= "**Generated:** " . date('Y-m-d H:i:s') . "\n\n";
$output .= "This document provides a complete reference of all database tables, columns, and relationships as implemented in the system.\n\n";
$output .= "**Total Tables:** " . count($tables) . "\n\n";
$output .= "---\n\n";

// Table of Contents
$output .= "## Table of Contents\n\n";
foreach ($tables as $tableName => $tableInfo) {
    $anchor = str_replace('_', '-', $tableName);
    $output .= "- [$tableName](#$anchor)\n";
}
$output .= "\n---\n\n";

// Detailed table information
foreach ($tables as $tableName => $tableInfo) {
    $output .= "## $tableName\n\n";
    
    if (!empty($tableInfo['migrations'])) {
        $output .= "**Defined in migrations:**\n";
        foreach ($tableInfo['migrations'] as $migration) {
            $output .= "- `$migration`\n";
        }
        $output .= "\n";
    }
    
    if (!empty($tableInfo['columns'])) {
        $output .= "### Columns\n\n";
        $output .= "| Column Name | Data Type | Nullable | Default | Unique | Enum Values |\n";
        $output .= "|------------|-----------|----------|---------|--------|-------------|\n";
        
        foreach ($tableInfo['columns'] as $colName => $colInfo) {
            $nullable = $colInfo['nullable'] ? 'Yes' : 'No';
            $unique = $colInfo['unique'] ? 'Yes' : 'No';
            $default = $colInfo['default'] ?: '-';
            $enumValues = $colInfo['enum_values'] ? implode(', ', $colInfo['enum_values']) : '-';
            
            $output .= "| `$colName` | {$colInfo['type']} | $nullable | $default | $unique | $enumValues |\n";
        }
        $output .= "\n";
    }
    
    if (!empty($tableInfo['foreign_keys'])) {
        $output .= "### Foreign Keys\n\n";
        $output .= "| Column | References Table | On Delete |\n";
        $output .= "|--------|------------------|----------|\n";
        
        foreach ($tableInfo['foreign_keys'] as $fk) {
            $output .= "| `{$fk['column']}` | `{$fk['references']}` | {$fk['on_delete']} |\n";
        }
        $output .= "\n";
    }
    
    if (!empty($tableInfo['unique_constraints'])) {
        $output .= "### Unique Constraints\n\n";
        foreach ($tableInfo['unique_constraints'] as $uniqueCol) {
            $output .= "- `$uniqueCol`\n";
        }
        $output .= "\n";
    }
    
    $output .= "---\n\n";
}

// Generate relationships summary
$output .= "## Relationships Summary\n\n";
foreach ($tables as $tableName => $tableInfo) {
    if (!empty($tableInfo['foreign_keys'])) {
        $output .= "### $tableName\n\n";
        foreach ($tableInfo['foreign_keys'] as $fk) {
            $output .= "- `$tableName`.`{$fk['column']}` → `{$fk['references']}`.id\n";
        }
        $output .= "\n";
    }
}

// Save to file
file_put_contents(__DIR__ . '/COMPLETE_SCHEMA_REFERENCE.md', $output);

// Generate JSON for programmatic comparison
$jsonOutput = json_encode($tables, JSON_PRETTY_PRINT);
file_put_contents(__DIR__ . '/schema.json', $jsonOutput);

echo "✓ Schema analysis complete!\n";
echo "✓ Generated: database/COMPLETE_SCHEMA_REFERENCE.md\n";
echo "✓ Generated: database/schema.json\n";
echo "\n";
echo "=== SUMMARY ===\n";
echo "Total tables: " . count($tables) . "\n";
echo "\n";
echo "Tables with most columns:\n";
$colCounts = [];
foreach ($tables as $tableName => $tableInfo) {
    $colCounts[$tableName] = count($tableInfo['columns']);
}
arsort($colCounts);
$top = array_slice($colCounts, 0, 10, true);
foreach ($top as $table => $count) {
    echo sprintf("  %-40s %3d columns\n", $table, $count);
}

