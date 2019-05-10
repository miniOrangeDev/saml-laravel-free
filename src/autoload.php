<?php

/** Some common constant variables to be used throughout the plugin */
if(!defined('MSSP_VERSION'))
define('MSSP_VERSION', '1.0.0');
if(!defined('MSSP_NAME'))
define('MSSP_NAME', basename(__DIR__));
if(!defined('MSSP_DIR'))
define('MSSP_DIR', __DIR__);
if(!defined('MSSP_TEST_MODE'))
define('MSSP_TEST_MODE', FALSE);

/**
 * This class is being used to auto include all files being used in the
 * plugin. Removes the pain of individually including all files. This class
 * loads the files only as the need arises.
 */
/*class SplClassLoader
{
    private $_fileExtension = '.php';
    private $_namespace;
    private $_includePath;
    private $_namespaceSeparator = '\\';

    public function __construct($ns = null, $includePath = null)
    {
        $this->_namespace = $ns;
        $this->_includePath = $includePath;
    }
*/
    /** Installs this class loader on the SPL autoload stack. */
    /*public function register()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }


    /**  Uninstalls this class loader from the SPL autoloader stack. */
   /* public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }


    /**
     * Loads the given class or interface.
     *
     * @param string $className The name of the class to load.
     * @return void
     */
  /*  public function loadClass($className)
    {
        if (null === $this->_namespace || $this->_namespace . $this->_namespaceSeparator ===
                substr($className, 0, strlen($this->_namespace . $this->_namespaceSeparator)))
        {
            $fileName = '';
            $namespace = '';
            if (false !== ($lastNsPos = strripos($className, $this->_namespaceSeparator))) {
                $namespace = strtolower(substr($className, 0, $lastNsPos));
                $className = substr($className, $lastNsPos + 1);
                $fileName = str_replace($this->_namespaceSeparator, DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
            }
            $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . $this->_fileExtension;
            $fileName = str_replace("miniorange",MSSP_NAME,$fileName); //repalce the idp namespace with plugin folder name
            require ($this->_includePath !== null ? $this->_includePath . DIRECTORY_SEPARATOR : '') . $fileName;
        }
    }
}

$idpClassLoader = new SplClassLoader('MiniOrange', realpath(__DIR__ . DIRECTORY_SEPARATOR . ".."));
$idpClassLoader->register();*/