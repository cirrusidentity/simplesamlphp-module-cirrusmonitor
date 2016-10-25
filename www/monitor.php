<?php

/**
 * Handle request for monitoring data.
 *
 * A request to monitor.php will display all monitoring status.
 * A request to monitor.php/metadata will dispaly monitoring status for only metadata.
 */

$component = 'ALL';
if (!array_key_exists('PATH_INFO', $_SERVER)) {
    $component = substr($_SERVER['PATH_INFO'], 1);
}


/* TODO: we may want to do this as a hook: SimpleSAML\Module::callHooks('cirrusmonitor', $someParams);
 *
 * That way other modules can respond to request for monitoring. Another option is to piggy back on
 * the 'sanitycheck' webhook, but provide a better interface for a monitoring solution to interpret the data.
 */


$module_config = SimpleSAML_Configuration::getConfig('module_cirrusmonitor.php');

$monitor = new sspmod_cirrusmonitor_Monitor($module_config);
//TODO: define what a response looks like
$response = $monitor->performCheck($component);

$config = SimpleSAML_Configuration::getInstance();
$t = new SimpleSAML_XHTML_Template($config, 'cirrusmonitor:monitor.tpl.php');
$t->data['someKey'] = 'someData';
$t->show();