<?php
namespace App\Console\Commands;
use Illuminate\Console\GeneratorCommand;

class MakeRepositoryCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository {name : repository name} 
                            {--sub-path=Eloquent : sub path in repository directory}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository class';
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Repository';
    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/repository.stub';
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
        return $rootNamespace.'\Repositories\\' . $this->option('sub-path');
    }
}