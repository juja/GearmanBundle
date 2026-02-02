<?php

/**
 * Gearman Bundle for Symfony2
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

namespace Mmoreram\GearmanBundle\Tests\Module;

use Doctrine\Common\Annotations\AnnotationReader;

use Mmoreram\GearmanBundle\Driver\Gearman\Job;
use Mmoreram\GearmanBundle\Driver\Gearman\Work as WorkAnnotation;
use Mmoreram\GearmanBundle\Module\WorkerClass;

/**
 * Tests JobClassTest class
 */
class WorkerClassTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var WorkAnnotation
     *
     * Worker annotation driver
     */
    private $workAnnotation;

    /**
     * @var \ReflectionClass
     *
     * Reflection Class
     */
    private $reflectionClassMock;

    /**
     * @var AnnotationReader
     *
     * Reader
     */
    private $doctrineAnnotationReaderMock;

    /**
     * @var string
     *
     * Class namespace
     */
    private $classNamespace = 'MyClassNamespace';

    /**
     * @var string
     *
     * Class name
     */
    private $className = 'myClass';

    /**
     * @var string
     *
     * Filename
     */
    private $fileName = 'myClass.php';

    /**
     * @var array
     *
     * Servers list
     */
    private $servers = [
        [
            'host'  =>  '192.168.1.1',
            'port'  =>  '8080',
        ],
    ];

    /**
     * @var array
     *
     * Default settings
     */
    private $defaultSettings = [
        'method'                         => 'doHigh',
        'iterations'                     => 100,
        'minimum_execution_time'         => null,
        'timeout'                        => null,
        'callbacks'                      => true,
        'jobPrefix'                      => null,
        'generate_unique_key'            => true,
        'workers_name_prepend_namespace' => true,
    ];

    /**
     * Setup
     */
    public function setUp(): void
    {
        $this->reflectionClassMock = $this
            ->getMockBuilder('\ReflectionClass')
            ->setConstructorArgs(['\Mmoreram\GearmanBundle\Tests\Service\Mocks\SingleCleanFile'])
            ->setMethods([
                'getName',
                'getNamespaceName',
                'getFileName',
                'getMethods',
                'getAttributes'
            ])
            ->getMock();


        $this->doctrineAnnotationReaderMock = $this
            ->getMockBuilder('Doctrine\Common\Annotations\AnnotationReader')
            ->disableOriginalConstructor()
            ->setMethods([
                'getMethodAnnotations',
            ])
            ->getMock();
    }

    /**
     * Testing scenario with all Job annotations filled
     *
     * All settings given in annotations should be considered to configure Job
     *
     * Also testing server definition in JobAnnotation as an array of arrays ( multi server )
     */
    public function testWorkerAnnotationsDefined()
    {
        $this->mockReflectionClassWithWorkerAnnotationWithoutMethods();
        $expectedWorkerConfig = [
            'namespace' => $this->classNamespace,
            'className' => $this->className,
            'fileName' => $this->fileName,
            'callableName' => $this->classNamespace . $this->workAnnotation->name,
            'description' => $this->workAnnotation->description,
            'service' => $this->workAnnotation->service,
            'servers' => $this->workAnnotation->servers,
            'iterations' => $this->workAnnotation->iterations,
            'minimumExecutionTime' => $this->workAnnotation->minimumExecutionTime,
            'timeout' => $this->workAnnotation->timeout,
            'jobs' => [],
        ];

        $workerClass = new WorkerClass(
            $this->workAnnotation,
            $this->reflectionClassMock,
            $this->servers,
            $this->defaultSettings
        );

        $this->assertEquals($expectedWorkerConfig, $workerClass->toArray());
    }

    public function testWorkerAnnotationsDefinedWithJobs()
    {
        $this->mockReflectionClassWithOneMethod();
        $jobData = [
            'name' => 'job-name-test',
            'description' => 'This is my own description',
            'iterations' => 10,
            'defaultMethod' => 'defaultMethodTest',
            'timeout' => 12,
            'minimumExecutionTime' => 13,
            'servers' => [[
                'host' => '192.168.1.2',
                'port' => '88',
            ],]
        ];
        $this->mockReflectionMethodWithJobData($jobData);
        $expectedWorkerConfig = [
            'namespace' => 'MyClassNamespace',
            'className' => 'myClass',
            'fileName' => 'myClass.php',
            'callableName' => 'MyClassNamespacemyOtherWorkerName',
            'description' => 'This is my own description',
            'service' => 'my.service',
            'servers' => [
                [
                    'host' => '10.0.0.2',
                    'port' => '80',
                ],
            ],
            'iterations' => 200,
            'minimumExecutionTime' => 0,
            'timeout' => 0,
            'jobs' => [
                [
                    'callableName' => 'job-name-test',
                    'methodName' => 'jobMethodName',
                    'realCallableName' => 'MyClassNamespacemyOtherWorkerName~job-name-test',
                    'jobPrefix' => null,
                    'realCallableNameNoPrefix' => 'MyClassNamespacemyOtherWorkerName~job-name-test',
                    'description' => 'This is my own description',
                    'iterations' => 10,
                    'minimumExecutionTime' => 13,
                    'timeout' => 12,
                    'servers' => [
                        [
                            'host' => '192.168.1.2',
                            'port' => '88',
                        ],
                    ],
                    'defaultMethod' => 'defaultMethodTest',
                ],
            ],
        ];

        $workerClass = new WorkerClass(
            $this->workAnnotation,
            $this->reflectionClassMock,
            $this->servers,
            $this->defaultSettings
        );

        $this->assertEquals($expectedWorkerConfig, $workerClass->toArray());
    }

    public function testWorkerAnnotationsDefinedWithJobs1()
    {
        $this->mockReflectionClassWithJobAttributes();
        $expectedWorkerConfig = [
            'namespace' => 'MyClassNamespace',
            'className' => 'myClass',
            'fileName' => 'myClass.php',
            'callableName' => 'MyClassNamespacemyOtherWorkerName',
            'description' => 'This is my own description',
            'service' => 'my.service',
            'servers' => [
                [
                    'host' => '10.0.0.2',
                    'port' => '80',
                ],
            ],
            'iterations' => 200,
            'minimumExecutionTime' => 0,
            'timeout' => 0,
            'jobs' => [
                [
                    'callableName' => 'job-name-test',
                    'methodName' => 'jobMethodName',
                    'realCallableName' => 'MyClassNamespacemyOtherWorkerName~job-name-test',
                    'jobPrefix' => null,
                    'realCallableNameNoPrefix' => 'MyClassNamespacemyOtherWorkerName~job-name-test',
                    'description' => 'This is my own description',
                    'iterations' => 10,
                    'minimumExecutionTime' => 13,
                    'timeout' => 12,
                    'servers' => [
                        [
                            'host' => '192.168.1.2',
                            'port' => '88',
                        ],
                    ],
                    'defaultMethod' => 'defaultMethodTest',
                ],
            ],
        ];

        $workerClass = new WorkerClass(
            $this->workAnnotation,
            $this->reflectionClassMock,
            $this->servers,
            $this->defaultSettings
        );

        $this->assertEquals($expectedWorkerConfig, $workerClass->toArray());
    }

    /**
     * Testing scenario with any Job annotation filled
     *
     * All settings set as default should be considered to configure Job
     *
     * Also testing empty server definition in JobAnnotation
     */
    public function testWorkerAnnotationsEmpty()
    {
        $this->mockReflectionClassWithoutWorkerAnnotationWithoutMethods();
        $expectedWorkerConfig = [
            'namespace' => $this->classNamespace,
            'className' => $this->className,
            'fileName' => $this->fileName,
            'callableName' => $this->className,
            'description' => WorkerClass::DEFAULT_DESCRIPTION,
            'service' => null,
            'servers' => $this->servers,
            'iterations' => $this->defaultSettings['iterations'],
            'minimumExecutionTime' => $this->defaultSettings['minimum_execution_time'],
            'timeout' => $this->defaultSettings['timeout'],
            'jobs' => [],
        ];

        $workerClass = new WorkerClass(
            $this->workAnnotation,
            $this->reflectionClassMock,
            $this->servers,
            $this->defaultSettings
        );

        $this->assertEquals($expectedWorkerConfig, $workerClass->toArray());
    }

    /**
     * Testing specific server scenario configured in Job annotations as a simple server
     */
    public function testCombinationServers()
    {
        $this->mockReflectionClassWithoutWorkerAnnotationWithoutMethods();
        $this->workAnnotation->servers = [
            'host'  =>  '10.0.0.2',
            'port'  =>  '80',
        ];
        $expectedWorkerConfig = [

            'namespace' => $this->classNamespace,
            'className' => $this->className,
            'fileName' => $this->fileName,
            'callableName' => $this->className,
            'description' => WorkerClass::DEFAULT_DESCRIPTION,
            'service' => null,
            'servers' => [$this->workAnnotation->servers],
            'iterations' => $this->defaultSettings['iterations'],
            'minimumExecutionTime' => $this->defaultSettings['minimum_execution_time'],
            'timeout' => $this->defaultSettings['timeout'],
            'jobs' => [],
        ];

        $workerClass = new WorkerClass(
            $this->workAnnotation,
            $this->reflectionClassMock,
            $this->servers,
            $this->defaultSettings
        );

        $this->assertEquals($expectedWorkerConfig, $workerClass->toArray());
    }

    /**
     * @return void
     */
    public function mockReflectionClassWithOneMethod(): void
    {
        $this
            ->reflectionClassMock
            ->method('getNamespaceName')
            ->will($this->returnValue($this->classNamespace));

        $this
            ->reflectionClassMock
            ->method('getName')
            ->will($this->returnValue($this->className));

        $this
            ->reflectionClassMock
            ->method('getFileName')
            ->will($this->returnValue($this->fileName));


        $this
            ->reflectionClassMock
            ->method('getAttributes')
            ->willReturn([]);

        $this->createWorkAnnotation([
            'name' => 'myOtherWorkerName',
            'description' => 'This is my own description',
            'iterations' => 200,
            'defaultMethod' => 'doHighBackground',
            'service' => 'my.service',
            'servers' => [[
                'host' => '10.0.0.2',
                'port' => '80',
            ]]
        ]);
    }

    public function createWorkAnnotation($annotationData): void
    {
        $this->workAnnotation = new WorkAnnotation($annotationData);
    }

    /**
     * @return void
     */
    public function mockReflectionClassWithWorkerAnnotationWithoutMethods(): void
    {
        $this
            ->reflectionClassMock
            ->method('getNamespaceName')
            ->will($this->returnValue($this->classNamespace));

        $this
            ->reflectionClassMock
            ->method('getName')
            ->will($this->returnValue($this->className));

        $this
            ->reflectionClassMock
            ->method('getFileName')
            ->will($this->returnValue($this->fileName));

        $this
            ->reflectionClassMock
            ->method('getMethods')
            ->will($this->returnValue([]));

        $this->createWorkAnnotation([
            'name' => 'myOtherWorkerName',
            'description' => 'This is my own description',
            'iterations' => 200,
            'defaultMethod' => 'doHighBackground',
            'service' => 'my.service',
            'servers' => [[
                'host' => '10.0.0.2',
                'port' => '80',
            ]]
        ]);
    }

    /**
     * @return void
     */
    public function mockReflectionClassWithoutWorkerAnnotationWithoutMethods(): void
    {
        $this->mockReflectionClassWithWorkerAnnotationWithoutMethods();
        $this->createWorkAnnotation([]);
    }

    /**
     * @return void
     */
    public function mockReflectionClassWithJobAttributes(): void
    {
        $this->reflectionClassMock
            ->method('getNamespaceName')
            ->will($this->returnValue($this->classNamespace));

        $this->reflectionClassMock
            ->method('getName')
            ->will($this->returnValue($this->className));

        $this->reflectionClassMock
            ->method('getFileName')
            ->will($this->returnValue($this->fileName));

        $this->mockReflectionMethodWithJobData([
            'name' => 'job-name-test',
            'description' => 'This is my own description',
            'iterations' => 10,
            'defaultMethod' => 'defaultMethodTest',
            'timeout' => 12,
            'minimumExecutionTime' => 13,
            'servers' => [[
                'host' => '192.168.1.2',
                'port' => '88',
            ],]
        ]);

        $this->reflectionClassMock
            ->method('getAttributes')
            ->willReturn([]);

        $this->createWorkAnnotation([
            'name' => 'myOtherWorkerName',
            'description' => 'This is my own description',
            'iterations' => 200,
            'defaultMethod' => 'doHighBackground',
            'service' => 'my.service',
            'servers' => [[
                'host' => '10.0.0.2',
                'port' => '80',
            ]]
        ]);
    }

    public function mockReflectionMethodWithJobData($jobData): void
    {
        $mockAttribute = $this
            ->getMockBuilder(\ReflectionMethod::class)
            ->disableOriginalConstructor()
            ->setMethods(['newInstance'])
            ->getMock();

        $mockAttribute
            ->method('newInstance')
            ->willReturn(new Job($jobData));

        $reflectionMethodMock = $this->createMock(\ReflectionMethod::class);
        $reflectionMethodMock
            ->method('getAttributes')
            ->with(Job::class, \ReflectionAttribute::IS_INSTANCEOF)
            ->willReturn([$mockAttribute]);

        $reflectionMethodMock
            ->method('getName')
            ->willReturn("jobMethodName");

        $this->reflectionClassMock
            ->method('getMethods')
            ->willReturn([$reflectionMethodMock]);
    }
}
