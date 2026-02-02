<?php

namespace Mmoreram\GearmanBundle\Tests\Functional\TestClassesAnnotations;

use Mmoreram\GearmanBundle\Driver\Gearman;

/**
 * @Gearman\Work(
 *     description = "Worker de prueba",
 *     defaultMethod = "doBackground",
 *     service="nombre.servicio"
 * )
 */
class WorkerTestClassAnnotations
{
    /**
     * @Gearman\Job(
     *      name = "job-de-prueba",
     *      description = "Descripción del job de prueba"
     *  )
     */
    public function jobTest()
    {

    }

}