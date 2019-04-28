<?php
/**
 * Created by PhpStorm.
 * User: KevinYan
 * Date: 2019/4/26
 * Time: 6:00 PM
 */

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeContractCommand extends  GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:contract {name : contracts name} 
                            {--sub-path= : sub path in contracts directory}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a contract interface';
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Contract';
    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/contract.stub';
    }
    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Contracts\\' . $this->option('sub-path');
    }
}
