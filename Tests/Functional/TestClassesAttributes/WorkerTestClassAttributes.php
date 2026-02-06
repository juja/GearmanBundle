<?php

namespace Mmoreram\GearmanBundle\Tests\Functional\TestClassesAttributes;

use Mmoreram\GearmanBundle\Driver\Gearman;

#[Gearman\Work(
    description   : "Worker de prueba",
    defaultMethod : "doBackground",
    service       : "nombre.servicio"
)]
class WorkerTestClassAttributes
{
    #[Gearman\Job(
        name        : "job-de-prueba",
        description : "Descripción del job de prueba"
    )]
    public function jobTest()
    {

    }

}