<?php
#ddev-generated

// ✅ Use environment variables for paths
$appRoot = $_ENV['DDEV_APPROOT'];
$siteName = $_ENV['DDEV_SITENAME'];

$scriptFile = "{$appRoot}/.ddev/redis/scripts/setup-redis-optimized-config.php";
$extraComposeFile = "{$appRoot}/.ddev/docker-compose.redis_extra.yaml";

$envFile = '.env.redis';
$isOptimized = false;

if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    $isOptimized = strpos($envContent, 'REDIS_OPTIMIZED=true') !== false;
}

if (!$isOptimized) {
    // Remove configuration files for non-optimized setup
    $configFiles = ['advanced', 'append', 'general', 'io', 'memory', 'network', 'security', 'snapshots'];

    foreach ($configFiles as $configFile) {
        $filePath = "{$appRoot}/.ddev/redis/{$configFile}.conf";
        if (file_exists($filePath) && strpos(file_get_contents($filePath), '#ddev-generated') !== false) {
            unlink($filePath);
        }
    }

    // Remove extra docker and script files
    $filesToRemove = [$extraComposeFile, $scriptFile];
    foreach ($filesToRemove as $file) {
        if (file_exists($file) && strpos(file_get_contents($file), '#ddev-generated') !== false) {
            echo "Removing {$file}\n";
            unlink($file);
        }
    }
    return 0;
}

// Generate optimized redis.conf with includes
$redisConfFile = "{$appRoot}/.ddev/redis/redis.conf";
if (file_exists($redisConfFile) && strpos(file_get_contents($redisConfFile), '#ddev-generated') !== false) {
    $redisConfContent = '# #ddev-generated
################################## INCLUDES ###################################

# Network
include /etc/redis/conf/network.conf

# General
include /etc/redis/conf/general.conf

# Snapshots
include /etc/redis/conf/snapshots.conf

# Security
include /etc/redis/conf/security.conf

# Memory management
include /etc/redis/conf/memory.conf

# CPU management
include /etc/redis/conf/io.conf

# Append mode
include /etc/redis/conf/append.conf

# Advanced config
include /etc/redis/conf/advanced.conf
';
    file_put_contents($redisConfFile, $redisConfContent);
}

//TODO: Shouldn't this be a static file instead of ugly generation?
// ✅ Generate docker-compose extra file using yaml_emit instead of heredoc
if (!file_exists($extraComposeFile) || strpos(file_get_contents($extraComposeFile), '#ddev-generated') !== false) {
    $dockerConfig = [
        'services' => [
            'redis' => [
                'deploy' => [
                    'resources' => [
                        'limits' => [
                            'cpus' => '2.5',
                            'memory' => '768M'
                        ],
                        'reservations' => [
                            'cpus' => '1.5',
                            'memory' => '512M'
                        ]
                    ]
                ]
            ]
        ]
    ];

    $yamlContent = "#ddev-generated\n" . yaml_emit($dockerConfig);
    file_put_contents($extraComposeFile, $yamlContent);
}

// Use absolute path for snapshots file
// Update snapshots.conf with project-specific dump filename
echo "Change the redis dump filename if applicable\n";
$snapshotsFile = "{$appRoot}/.ddev/redis/snapshots.conf";
if (is_file($snapshotsFile)) {
    $content = file_get_contents($snapshotsFile);
    if ($content !== false) {
        $newContent = str_replace('REPLACE_ME', $siteName, $content);
        if ($newContent !== $content) {
            file_put_contents($snapshotsFile, $newContent);
        }
    }
} else {
    echo "Warning: snapshots file not found at {$snapshotsFile}\n";
}

// Remove this script file after execution
if (file_exists($scriptFile) && strpos(file_get_contents($scriptFile), '#ddev-generated') !== false) {
    echo "Removing {$scriptFile}\n";
    unlink($scriptFile);
}

return 0;
?>
