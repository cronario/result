<?php

namespace Result;

use RecursiveRegexIterator;
use ReflectionClass;
use RegexIterator;
use SplFileObject;

/**
 * Class MapBuilder
 *
 * This class builds result map.
 * It iterates over param searchPath
 * and find all instances of \Result\ResultException
 *
 * @package Result
 */
class MapBuilder
{
    private $map = [];
    private $file = [];

    private static $instance;

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Build result map in provided path and write result to file
     *
     * @param string $file
     * @param array|string $searchPath
     */
    public static function build($file, $searchPath)
    {
        $builder = self::getInstance();

        $searchPath = empty($searchPath) ? __DIR__ : $searchPath;
        $searchPath = (array) $searchPath;

        $builder->setFile($file);
        $builder->readMap();

        foreach ($searchPath as $folder) {
            $files = $builder->getIterator($folder);
            $builder->iterateOverFiles($files);
        }

        $builder->save();
    }

    /**
     * @param $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * Add new result class to map.
     * If file is empty begin numeration from index 1.
     * Zero index reserved for \Result\ResultException
     *
     * @param $class
     */
    public function pushResult($class)
    {
        if (!$this->hasResult($class)) {
            $this->map[$class] = empty($this->map) ? 1
                : max(self::getResults()) + 1;
        }
    }

    /**
     * Check if class already exists in class map
     *
     * @param $class String
     *
     * @return bool
     */
    public function hasResult($class)
    {
        return array_key_exists($class, self::getResults());
    }

    /**
     * Return result set
     *
     * @return array
     */
    public function getResults()
    {
        return $this->map;
    }

    /**
     * Save result map to file
     */
    public function save()
    {
        $results
            = "<?php \n return " . var_export(self::getResults(), true) . ";";
        file_put_contents($this->file, $results);
    }

    /**
     * Reads result map from file
     */
    public function readMap()
    {
        $fileData = include $this->file;
        $this->map = !is_array($fileData) ? $this->map : $fileData;
    }

    /**
     * @param $folder
     *
     * @return RegexIterator
     */
    protected function getIterator($folder)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folder,
                \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST,
            \RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
        );
        $files = new RegexIterator($iterator, '/^.+Exception\.php$/i', RecursiveRegexIterator::GET_MATCH);

        return $files;
    }


    /**
     * @param $file
     *
     * @return SplFileObject
     */
    protected function getFile($file)
    {
        return new SplFileObject($file[0]);
    }

    /**
     * @param \SplFileObject $file
     *
     * @return mixed
     */
    protected function extractNamespace($file)
    {
        $namespace = false;

        foreach ($file as $line) {
            if (preg_match('/^namespace (?P<namespace>.*);$/i', $line, $matches)) {
                $namespace = $matches['namespace'];
                continue;
            }
        }
        return $namespace;
    }

    /**
     * @param \SplFileObject $file
     * @param string $namespace
     */
    protected function checkInstance($file, $namespace)
    {
        $class = $namespace . '\\' . $file->getBasename('.php');
        $reflection = new ReflectionClass($class);

        if ($reflection->isSubclassOf('\\Result\\ResultException')) {
            self::getInstance()->pushResult($class);
        }
    }

    /**
     * @param $files
     *
     * @internal param $file
     */
    protected function iterateOverFiles($files)
    {
        foreach ($files as $file) {
            $file = $this->getFile($file);

            if ($namespace = $this->extractNamespace($file)) {
                $this->checkInstance($file, $namespace);
            }
        }
    }

}
