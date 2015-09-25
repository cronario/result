<?php

namespace Result;

use RecursiveRegexIterator;
use ReflectionClass;
use RegexIterator;
use SplFileObject;

/**
 * Class MapBuilder
 *
 * @package Result
 */
class MapBuilder
{
    private static $_map = [];
    private static $_file = [];

    /**
     * Build result map in provided path and write result to file
     *
     * @param string $file
     * @param array|string $searchPath
     */
    public static function build($file, $searchPath)
    {
        $searchPath = empty($searchPath) ? __DIR__ : $searchPath;
        $searchPath = (array) $searchPath;

        $includePath = implode(PATH_SEPARATOR, $searchPath);
        set_include_path($includePath . get_include_path());

        self::setFile($file);
        self::readMap();

        foreach ((array) $searchPath as $folder) {
            $iterator = self::getIterator($folder);
            $files = new RegexIterator($iterator, '/^.+Exception\.php$/i', RecursiveRegexIterator::GET_MATCH);

            /**
             * @var  $file \SplFileInfo
             */
            foreach ($files as $file) {
                $file = $file[0];
                $className = basename($file, '.php');
                $file = new SplFileObject($file);
                $namespace = null;
                foreach ($file as $line) {
                    if (preg_match('/^namespace (?P<namespace>.*);$/i',
                        $line, $matches)) {
                        $namespace = ($matches['namespace']);
                        continue;
                    }
                }
                if (null !== $namespace) {
                    $class = $namespace . '\\' . $className;
                    $reflection = new ReflectionClass($class);
                    if ($reflection->isSubclassOf('\\Result\\ResultException')) {
                        self::addResult($class);
                    }
                }
            }
        }
        self::save();
    }

    public static function setFile($file)
    {
        self::$_file = $file;
    }

    /**
     * Add new result class to map.
     * If file is empty begin numeration from index 1.
     * Zero index reserved for \Result\ResultException
     *
     * @param $class
     */
    public static function addResult($class)
    {
        if (!self::hasResult($class)) {
            self::$_map[$class] = empty(self::$_map) ? 1
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
    public static function hasResult($class)
    {
        return array_key_exists($class, self::getResults());
    }

    /**
     * Return result set
     *
     * @return array
     */
    public static function getResults()
    {
        return self::$_map;
    }

    /**
     * Save result map to file
     */
    public static function save()
    {
        $results
            = "<?php \n return " . var_export(self::getResults(), true) . ";";
        file_put_contents(self::$_file, $results);
    }

    /**
     * Reads result map from file
     */
    public static function readMap()
    {
        $fileData = include self::$_file;
        self::$_map = !is_array($fileData) ? self::$_map : $fileData;
    }

    /**
     * @param $folder
     *
     * @return \RecursiveIteratorIterator
     */
    protected static function getIterator($folder)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folder,
                \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST,
            \RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
        );
        return $iterator;
    }
}
