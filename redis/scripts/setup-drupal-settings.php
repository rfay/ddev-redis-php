<?php
#ddev-generated

// ✅ Use environment variables instead of manual config parsing
$projectType = $_ENV['DDEV_PROJECT_TYPE'];
$docroot = $_ENV['DDEV_DOCROOT'];
$appRoot = $_ENV['DDEV_APPROOT'];

// Check if this is a supported Drupal version
if (strpos($projectType, 'drupal') !== 0 || preg_match('/^drupal[67]$/', $projectType)) {
    // Remove files for non-applicable project types
    $filesToRemove = [
        'redis/scripts/settings.ddev.redis.php',
        'redis/scripts/setup-drupal-settings.php'
    ];
    
    foreach ($filesToRemove as $file) {
        if (file_exists($file) && strpos(file_get_contents($file), '#ddev-generated') !== false) {
            echo "Removing {$file} as not applicable\n";
            unlink($file);
        }
    }
    return;
}

// ✅ Use processed configuration instead of ddev debug configyaml
$config = yaml_parse_file('.ddev-config/project_config.yaml');
if (isset($config['disable_settings_management']) && $config['disable_settings_management'] === true) {
    return;
}

// Copy the settings file to the appropriate Drupal location
$sourceFile = __DIR__ . '/settings.ddev.redis.php';
$targetDir = "{$appRoot}/{$docroot}/sites/default";
$targetFile = "{$targetDir}/settings.ddev.redis.php";

if (!copy($sourceFile, $targetFile)) {
    echo "Error: Failed to copy {$sourceFile} to {$targetFile}\n";
    return 1;
}

// Add include to settings.php if not already present
$settingsFile = "{$targetDir}/settings.php";
echo "Settings file name: {$settingsFile}\n";

if (file_exists($settingsFile)) {
    $settingsContent = file_get_contents($settingsFile);
    
    // Check if the include is already present
    if (strpos($settingsContent, 'settings.ddev.redis.php') === false) {
        $includeCode = "
// Include settings required for Redis cache.
if (getenv('IS_DDEV_PROJECT') == 'true' && file_exists(__DIR__ . '/settings.ddev.redis.php')) {
  include __DIR__ . '/settings.ddev.redis.php';
}";
        file_put_contents($settingsFile, $includeCode, FILE_APPEND);
    }
}
?>