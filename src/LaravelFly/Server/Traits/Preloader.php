<?php

/**
 * from:
 * https://github.com/laravel/framework/blob/101cb80750cb81fe26cd38686b8464a7e943330d/src/Illuminate/Foundation/Console/OptimizeCommand.php
 *
 * [Laravel 5.6 Will Remove the Artisan Optimize Command](https://laravel-news.com/laravel-5-6-removes-artisan-optimize)
 * because of PHP op-code caching
 * but LaravelFly uses it,
 * because LaravelFly uses `opcache_reset()` in each worker
 */

namespace LaravelFly\Server\Traits;

use ClassPreloader\Factory;
use ClassPreloader\Exceptions\VisitorExceptionInterface;

Trait Preloader
{
    protected $preloadFile;

    protected function getCachedCompilePath()
    {
        if ($this->preloadFile === null)
            $this->preloadFile = $this->path('bootstrap/cache/laravelfly_preload.php');

        return $this->preloadFile;
    }

    public function loadCachedCompileFile()
    {
        if ($this->getConfig('compile') === 'force' ||
            !is_file($this->getCachedCompilePath()) ||
            filemtime($this->getCachedCompilePath()) < filemtime($this->path('composer.lock'))) {
            $this->compileClasses();
        }

        echo "[INFO] include: {$this->preloadFile}", PHP_EOL;
        include $this->preloadFile;
    }

    /**
     * Generate the compiled class file.
     *
     * @return void
     */
    protected function compileClasses()
    {
        echo "[INFO] compile classes to preload, please wait a moment", PHP_EOL;

        $preloader = (new Factory)->create(['skip' => true]);
        $handle = $preloader->prepareOutput($this->getCachedCompilePath());
        foreach ($this->getClassFiles() as $file) {
            try {
                // echo "$file\n";
                fwrite($handle, $preloader->getCode($file, false) . "\n");
            } catch (VisitorExceptionInterface $e) {
                //
            }
        }
        fclose($handle);
    }

    /**
     * Get the classes that should be combined and compiled.
     *
     * @return array
     */
    protected function getClassFiles()
    {
        $core = require __DIR__ . '/preloader_config_onlymapmode.php';

        // Map mode has loaded fly files and related files, so non-Map mode can load more files now
        if ($this->getConfig('mode') !== 'Map') {
            $core = array_merge($core, require __DIR__ . '/preloader_config_more.php');
            if(!$this->getConfig('log_cache')){
                $core[]= $this->root . '/vendor/monolog/monolog/src/Monolog/Handler/HandlerInterface.php';
                $core[]= $this->root . '/vendor/monolog/monolog/src/Monolog/Handler/AbstractHandler.php';
                $core[]= $this->root . '/vendor/monolog/monolog/src/Monolog/Handler/AbstractProcessingHandler.php';
                $core[]= $this->root . '/vendor/monolog/monolog/src/Monolog/Handler/StreamHandler.php';
            }
        }

        $files = array_merge($core, $this->getConfig('compile_files') ?? []);
        return array_map('realpath', $files);
    }


}

