<?php

/**
 * Handle request for monitoring data.
 *
 * A request to monitor.php will display all monitoring status.
 * A request to monitor.php/metadata will display monitoring status for only metadata.
 */

use SimpleSAML\Configuration;
use SimpleSAML\Module\cirrusmonitor\metadata\MonitorMetadata;

$component = 'ALL';
if (array_key_exists('PATH_INFO', $_SERVER)) {
    $component = substr($_SERVER['PATH_INFO'], 1);
}

/* TODO: we may want to do this as a hook: SimpleSAML\Module::callHooks('cirrusmonitor', $someParams);
 *
 * That way other modules can respond to request for monitoring. Another option is to piggy back on
 * the 'sanitycheck' webhook, but provide a better interface for a monitoring solution to interpret the data.
 */

$module_config = Configuration::getConfig('module_cirrusmonitor.php');

$monitor_config = $module_config->getConfigItem('metadata');
$monitor = new MonitorMetadata($monitor_config);
//TODO: define what a response looks like
$response = $monitor->performCheck();

// At some future point there will be more monitoring classes and this would be the aggregate
$overallResponse = [
    'overallStatus' => $response['overallStatus'],
    'metadata' => $response,
];

echo "<pre>";
echo json_encode($overallResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
echo "</pre>";