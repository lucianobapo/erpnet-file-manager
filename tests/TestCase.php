<?php

namespace ErpNET\Tests;

//use ErpNET\App\Application;
//use ErpNET\Tests\Classes\Traits\DatabaseTransactions;
//use ErpNET\Tests\Classes\Traits\TestPrepare;
use Exception;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as Test;

class TestCase extends Test
{
//    use DatabaseTransactions;
//    use TestPrepare;
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    protected $testClass = null;

    protected $repo = null;

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
//        putenv('DB_DEFAULT=sqlite_testing');
//        $app = require __DIR__.'/../../laravel-delivery24horas-api/bootstrap/app.php';
        $app = require __DIR__.'/../bootstrap/app.php';
//        $app = require __DIR__.'/../vendor/laravel/laravel/bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();
        return $app;
    }

    /**
     * Default preparation for each test
     */
    public function setUp()
    {
        if (! $this->app) {
            $this->refreshApplication();
        }

        $this->loadConfig();
        $this->loadRegisterProviders();
        $this->loadSignatures();
        $this->loadRepo();
        parent::setUp();

        $this->prepareForTests();
    }

    /**
     * Migrates the database and set the mailer to 'pretend'.
     * This will cause the tests to run quickly.
     */
    protected function prepareForTests()
    {
//        if (!is_null($this->app) && !is_null($this->testClass) && is_null($this->repo)) {
//            try {
//                $this->repo = $this->app->make($this->testClass);
//            } catch (Exception $e) {
//                var_dump(get_class($this));
//                var_dump($e->getMessage());
//                $this->repo = null;
////                throw $e;
//            }
//
//        }
//        if (!is_null($this->repo)) {
//            var_dump(($this->testClass));
//            var_dump(get_class($this));
//        }
//        Artisan::call('migrate');
//        Mail::pretend(true);

//        $factory = new FactoryMuffin();
//        $class = $this->app->make(\ErpNET\App\Models\RepositoryLayer\OrderRepositoryInterface::class);
//        dd(get_class($class));
//        dd(($class->model));



//        \League\FactoryMuffin\Facade::setCustomSetter(function ($object, $name, $value) {
//            dd($object);
//            call_user_func([$object, "set".ucfirst($name)], $value);
//        });



//        $this->factory = new \League\FactoryMuffin\Facade;
//        \League\FactoryMuffin\Facade::setCustomMaker(function ($class) { return new $class('example'); });

//        dd($this->factory);
//        $this->factory->setCustomSetter(function($entity, $name, $value) {
//            call_user_func([$entity, "set".ucfirst($name)], $value);
//        });


//        $this->factory->setCustomSaver(function($entity) use ($em) {
//            $em->persist($entity);
//            $em->flush();
//            return true;
//        });
//
//        $this->factory->setCustomDeleter(function($entity) use ($em) {
//            // entity may already be deleted if Doctrine transaction is closed
//            if (!$em->contains($entity)) {
//                return true;
//            }
//            $em->remove($entity);
//            $em->flush($entity);
//            return true;
//        });


//        dd(get_class($class->model));
//        dd($message);
//        dd($message->getRepository());

    }

    public function testApplication()
    {
        if (is_null($this->testClass))
            $this->assertTrue($this->app instanceof Application);
    }
}
