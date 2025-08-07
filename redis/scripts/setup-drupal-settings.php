<?php
#ddev-generated

// Read DDEV configuration to get project type
$config = yaml_parse_file('/mnt/ddev_config/config.yaml');
$projectType = $config['type'] ?? '';

// Skip if not Drupal 8+ project
if (!str_starts_with($projectType, 'drupal') || 
    in_array($projectType, ['drupal6', 'drupal7'])) {
    
    // Remove files if not applicable
    $filesToRemove = [
        '/mnt/ddev_config/redis/scripts/settings.ddev.redis.php',
        '/mnt/ddev_config/redis/scripts/setup-drupal-settings.sh'
    ];
    
    foreach ($filesToRemove as $file) {
        if (file_exists($file) && 
            strpos(file_get_contents($file), '#ddev-generated') !== false) {
            echo "Removing $file as not applicable\n";
            unlink($file);
        }
    }
    exit(0);
}

// Check if settings management is disabled
if (isset($config['disable_settings_management']) && $config['disable_settings_management'] === true) {
    exit(0);
}

// Copy settings file to Drupal sites/default
$sourceFile = '/mnt/ddev_config/redis/scripts/settings.ddev.redis.php';
$targetDir = '/var/www/html/' . ($config['docroot'] ?? 'web') . '/sites/default';
$targetFile = $targetDir . '/settings.ddev.redis.php';

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

copy($sourceFile, $targetFile);

// Update settings.php to include Redis settings
$settingsFile = $targetDir . '/settings.php';
echo "Settings file name: $settingsFile\n";

if (file_exists($settingsFile)) {
    $settingsContent = file_get_contents($settingsFile);
    
    if (strpos($settingsContent, 'settings.ddev.redis.php') === false) {
        $redisInclude = "\n// Include settings required for Redis cache.\n";
        $redisInclude .= "if (getenv('IS_DDEV_PROJECT') == 'true' && file_exists(__DIR__ . '/settings.ddev.redis.php')) {\n";
        $redisInclude .= "  include __DIR__ . '/settings.ddev.redis.php';\n";
        $redisInclude .= "}\n";
        
        file_put_contents($settingsFile, $redisInclude, FILE_APPEND);
    }
}