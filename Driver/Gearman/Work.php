<?php

namespace Mmoreram\GearmanBundle\Driver\Gearman;

#[\Attribute]
class Work
{
    /**
     * Name of worker
     */
    public ?string $name = null;

    /**
     * Description of Worker
     */
    public ?string $description = null;

    /**
     * Number of iterations specified for all jobs inside Work
     */
    public ?int $iterations = null;

    /**
     * Servers assigned for all jobs of this work to be executed
     *
     * @var mixed
     */
    public $servers;

    /**
     * Default method to call for all jobs inside this work
     */
    public ?string $defaultMethod = null;

    /**
     * Default timeout in seconds for worker idle time
     */
    public ?int $timeout = null;

    /**
     * Default number of seconds the execution must run before being allowed to terminate
     */
    public ?int $minimumExecutionTime = null;

    /**
     * Service typeof Class. If it's defined, object will be instanced throught
     * service dependence injection.
     * Otherwise, class will be instance with new() method
     */
    public ?string $service = null;

    public function __construct(?string $name = null, ?string $description = null, ?string $defaultMethod = null, ?string $service = null, ?int $iterations = null, ?int $timeout = null, ?int $minimumExecutionTime = null, $servers = null)
    {
        $this->name = $name;
        $this->description = $description;
        $this->defaultMethod = $defaultMethod;
        $this->service = $service;
        $this->iterations = $iterations;
        $this->timeout = $timeout;
        $this->minimumExecutionTime = $minimumExecutionTime;
        $this->servers = $servers;
    }
}
