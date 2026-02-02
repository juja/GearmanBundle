<?php

namespace Mmoreram\GearmanBundle\Driver\Gearman;


#[\Attribute]
class Job
{
    /**
     * Method name to assign into job
     */
    public ?string $name;

    /**
     * Description of Job
     */
    public ?string $description = null;

    /**
     * Number of iterations specified for this job
     */
    public ?int $iterations = null;

    /**
     * Servers assigned for this job to be executed
     *
     * @var mixed
     */
    public $servers;

    /**
     * Default method to call for this job
     */
    public ?string $defaultMethod = null;
    public ?int $timeout = null;
    /**
     * Number of seconds the execution must run before being allowed to terminate
     */
    public ?int $minimumExecutionTime = null;

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
