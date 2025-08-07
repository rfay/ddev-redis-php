<?php
#ddev-generated

// Check if redis-optimized flag is set
// Since we can't easily call ddev dotenv from PHP container, check for the env file directly
$envFile = '/var/www/html/.ddev/.env.redis';
$isOptimized = false;

if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    $isOptimized = strpos($envContent, 'REDIS_OPTIMIZED=true') !== false;
}

$scriptFile = "/var/www/html/.ddev/redis/scripts/setup-redis-optimized-config.sh";
$extraDockerFile = "/var/www/html/.ddev/docker-compose.redis_extra.yaml";

if (!$isOptimized) {
    // Remove optimized config files
    $configFiles = ['advanced', 'append', 'general', 'io', 'memory', 'network', 'security', 'snapshots'];
    foreach ($configFiles as $file) {
        $filePath = "/mnt/ddev_config/redis/{$file}.conf";
        if (file_exists($filePath) && 
            strpos(file_get_contents($filePath), '#ddev-generated') !== false) {
            unlink($filePath);
        }
    }
    
    // Remove extra docker file and script
    $extraFiles = [$extraDockerFile, $scriptFile];
    
    foreach ($extraFiles as $file) {
        if (file_exists($file) && 
            strpos(file_get_contents($file), '#ddev-generated') !== false) {
            echo "Removing $file\n";
            unlink($file);
        }
    }
    exit(0);
}

// Generate optimized redis.conf
$redisConf = '/mnt/ddev_config/redis/redis.conf';
if (file_exists($redisConf) && 
    strpos(file_get_contents($redisConf), '#ddev-generated') !== false) {
    
    $optimizedConfig = "# #ddev-generated\n";
    $optimizedConfig .= "################################## INCLUDES ###################################\n\n";
    $optimizedConfig .= "# Network\n";
    $optimizedConfig .= "include /etc/redis/conf/network.conf\n\n";
    $optimizedConfig .= "# General\n";
    $optimizedConfig .= "include /etc/redis/conf/general.conf\n\n";
    $optimizedConfig .= "# Snapshots\n";
    $optimizedConfig .= "include /etc/redis/conf/snapshots.conf\n\n";
    $optimizedConfig .= "# Security\n";
    $optimizedConfig .= "include /etc/redis/conf/security.conf\n\n";
    $optimizedConfig .= "# Memory management\n";
    $optimizedConfig .= "include /etc/redis/conf/memory.conf\n\n";
    $optimizedConfig .= "# CPU management\n";
    $optimizedConfig .= "include /etc/redis/conf/io.conf\n\n";
    $optimizedConfig .= "# Append mode\n";
    $optimizedConfig .= "include /etc/redis/conf/append.conf\n\n";
    $optimizedConfig .= "# Advanced config\n";
    $optimizedConfig .= "include /etc/redis/conf/advanced.conf\n";
    
    file_put_contents($redisConf, $optimizedConfig);
}

// Generate docker-compose.redis_extra.yaml
if (!file_exists($extraDockerFile) || 
    strpos(file_get_contents($extraDockerFile), '#ddev-generated') !== false) {
    
    $extraConfig = "#ddev-generated\n";
    $extraConfig .= "services:\n";
    $extraConfig .= "  redis:\n";
    $extraConfig .= "    deploy:\n";
    $extraConfig .= "      resources:\n";
    $extraConfig .= "        limits:\n";
    $extraConfig .= "          cpus: \"2.5\"\n";
    $extraConfig .= "          memory: \"768M\"\n";
    $extraConfig .= "        reservations:\n";
    $extraConfig .= "          cpus: \"1.5\"\n";
    $extraConfig .= "          memory: \"512M\"\n";
    
    file_put_contents($extraDockerFile, $extraConfig);
}

// Update snapshots.conf with site name
$config = yaml_parse_file('/mnt/ddev_config/config.yaml');
$siteName = $config['name'] ?? 'default';

$snapshotsFile = '/mnt/ddev_config/redis/snapshots.conf';
if (file_exists($snapshotsFile)) {
    $content = file_get_contents($snapshotsFile);
    $content = str_replace('REPLACE_ME', $siteName, $content);
    file_put_contents($snapshotsFile, $content);
    echo "Change the redis dump filename if applicable\n";
}

// Remove the setup script itself
if (file_exists($scriptFile) && 
    strpos(file_get_contents($scriptFile), '#ddev-generated') !== false) {
    echo "Removing $scriptFile\n";
    unlink($scriptFile);
}