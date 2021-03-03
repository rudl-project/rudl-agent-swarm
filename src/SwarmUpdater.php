<?php


namespace Rudl\Agent\Swarm;


use Rudl\LibGitDb\RudlGitDbClient;
use Rudl\LibGitDb\Type\SwarmAgent\T_SwarmObj;

class SwarmUpdater
{

    public function __construct(
        public RudlGitDbClient $gitDb
    ){}


    private function performDockerLogin(T_SwarmObj $swarmObj)
    {
        foreach ($swarmObj->logins as $login) {
            echo "Docker login to '$login->registry' User: '$login->user'...";
            phore_proc("sudo /usr/bin/docker login -u :user --password-stdin :registry", [
                "registry" => $login->registry,
                "user" => $login->user
            ])->exec()->write($login->passwd)->close()->wait();
            echo "Done\n";
        }
    }


    private function performDockerStackDeploy(T_SwarmObj $swarmObj, string $path)
    {
        foreach ($swarmObj->stacks as $stack) {
            echo "Updating stack '$stack->name'...";
            if ($stack->online) {
                phore_exec("sudo /usr/bin/docker stack deploy --prune --with-registry-auth :stackName -c :file", [
                    "stackName" => $stack->name,
                    "file" => $path . "/" . $stack->compose_file
                ]);
            } else {
                phore_exec("sudo /usr/bin/docker stack rm :stackName", [
                    "stackName" => $stack->name
                ]);
            }
            echo "Done\n";
        }
    }


    public function __invoke()
    {
        $swarmObj = $this->gitDb->listObjects(SWARM_SCOPE)->getObject(SWARM_CONF_OBJECT)?->hydrate(T_SwarmObj::class);

        if ($swarmObj === null)
            throw new \InvalidArgumentException("SwarmConfig Object '" . SWARM_CONF_OBJECT . "' not found in scope '" . SWARM_SCOPE . "'");

        assert($swarmObj instanceof T_SwarmObj);

        $path = "/tmp/" . SWARM_SCOPE;

        $this->gitDb->syncObjects(SWARM_SCOPE, $path);

        if ($swarmObj->logins !== null) {
            $this->performDockerLogin($swarmObj);
        }

        $this->performDockerStackDeploy($swarmObj, $path);

    }

}