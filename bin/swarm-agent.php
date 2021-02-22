<?php
namespace App;

use Rudl\Agent\Swarm\SwarmUpdater;
use Rudl\CertIssuer\CertIssuerUpdater;
use Rudl\LibGitDb\RudlGitDbClient;
use Rudl\LibGitDb\UpdateRunner;

require __DIR__ . "/../vendor/autoload.php";


$gitDb = new RudlGitDbClient();
try {
    $gitDb->loadClientConfigFromEnv();
} catch (\Exception $e) {
    echo "\n\nEMERGENCY! EMERGENCY! EMERGENCY! EMERGENCY! EMERGENCY! EMERGENCY !EMERGENCY! EMERGENCY! EMERGENCY! \n\n";
    echo "LoadSystemConfig failed: " . $e->getMessage() . "\n";
    echo "\nThis is a permanent configuration error! Please correct environment and redeploy!\n\n";
    echo "\nThis system will shutdown in 30sec\n";
    sleep(30);
    throw $e;
}


$runner = new UpdateRunner($gitDb);

$runner->run(new SwarmUpdater($gitDb));