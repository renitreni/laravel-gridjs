<?php

namespace Throwexceptions\LaravelGridjs;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Pluralizer;

class GridjsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gridjs:make-builder {class-name} {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will create dynamic class builder.';

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    private Filesystem $files;

    /**
     * Create a new command instance.
     * @param  Filesystem  $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = $this->getSourceFilePath();

        $this->makeDirectory(dirname($path));

        $contents = $this->getSourceFile();

        if (! $this->files->exists($path)) {
            $this->files->put($path, $contents);
            $this->info("File : {$path} created");
        } else {
            $this->info("File : {$path} already exits");
        }

    }

    /**
     * Return the stub file path
     * @return string
     *
     */
    public function getStubPath(): string
    {
        return __DIR__.'/stubs/Gridjs.stub';
    }

    /**
     **
     * Map the stub variables present in stub to its value
     *
     * @return array
     */
    public function getStubVariables(): array
    {
        return [
            'NAMESPACE'  => 'Gridjs',
            'MODEL'      => $this->getSingularClassName($this->argument('model')),
            'CLASS_NAME' => $this->getSingularClassName($this->argument('class-name')),
        ];
    }

    /**
     * Get the stub path and the stub variables
     *
     * @return mixed
     */
    public function getSourceFile(): mixed
    {
        return $this->getStubContents($this->getStubPath(), $this->getStubVariables());
    }

    /**
     * Replace the stub variables(key) with the desire value
     *
     * @param $stub
     * @param  array  $stubVariables
     * @return mixed
     */
    public function getStubContents($stub, array $stubVariables = []): mixed
    {
        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace) {
            $contents = str_replace('$'.$search.'$', $replace, $contents);
        }

        return $contents;

    }

    /**
     * Get the full path of generate class
     *
     * @return string
     */
    public function getSourceFilePath(): string
    {
        return base_path('Gridjs').'/'.$this->getSingularClassName($this->argument('class-name')).'Gridjs.php';
    }

    /**
     * Return the Singular Capitalize Name
     * @param $model
     * @return string
     */
    public function getSingularClassName($model): string
    {
        return ucwords(Pluralizer::singular($model));
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return string
     */
    protected function makeDirectory(string $path): string
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }

        return $path;
    }

}
