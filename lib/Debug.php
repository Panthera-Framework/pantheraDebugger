<?php
/**
 * Panthera Debugger
 *
 * @package Panthera\core\components\debugger
 * @author Damian Kęska <damian.keska@fingo.pl>
 */

if (!class_exists('pSingleton'))
	include str_replace(basename(__FILE__), 'Singleton.php', __FILE__);

if (!class_exists('pHooks'))
	include str_replace(basename(__FILE__), 'Hooks.php', __FILE__);

if (!class_exists('arrays'))
    include str_replace(basename(__FILE__), 'arrays.module.php', __FILE__);

if (!class_exists('writableJSON'))
    include str_replace(basename(__FILE__), 'rwjson.module.php', __FILE__);

/**
 * Panthera Debugger
 *
 * @package Panthera\core\components\debugger
 * @author Damian Kęska <damian.keska@fingo.pl>
 */

class pantheraDebugger extends pSingleton
{
	protected $log = array();
	public $timer = null;
	public $printOutput = false;
	public $enabled = false;
	public $overlayEnabled = false;
	public $minPriority = 0;
	public $tpl = null;
	public $templateList = array();
	public $popupDisplayed = false;
	public $plugins = array();
	public static $debuggerPath = '';
	public $inline = array();
	public $debuggerWebroot = '';
	public $config = array();
    public $db = array();

	/**
	 * Constructor, creates also a template engine object
	 *
	 * @author Damian Kęska
	 */

	public function __construct()
	{
		// calculate paths
		$f = realpath(__FILE__);
		$documentRoot = realpath($_SERVER['DOCUMENT_ROOT']);
		$fileName = realpath($_SERVER['SCRIPT_FILENAME']);

		$parsed = parse_url($_SERVER['REQUEST_URI']);
		$webrootPrefix = str_replace(basename($parsed['path']), '', $parsed['path']);

		$realRoot = str_replace($_SERVER['SCRIPT_NAME'], '', $fileName);
		$debuggerRoot = str_replace($realRoot, '', $f);
		$debuggerRoot = str_replace('/lib/Debug.php', '', $debuggerRoot);
		$this->debuggerWebroot = $debuggerRoot;
		self::$debuggerPath = realpath(str_replace(basename(__FILE__), '/..', __FILE__));

		// check defined constants
		if (defined('DEBUG_ENABLE_LOGGING') and constant('DEBUG_ENABLE_LOGGING'))
			$this -> enabled = true;

		if (defined('DEBUG_ENABLE_OVERLAY') and constant('DEBUG_ENABLE_OVERLAY'))
			$this -> overlayEnabled = true;

		// run overlay
		if ($this -> overlayEnabled)
		{
			pHooks::addOption('template.display', array($this, 'addTemplate'));
			pHooks::addOption('template.fetch', array($this, 'addTemplate'));

			$this -> loadConfig(self::$debuggerPath. '/config.json');
		}

        if (!is_file(self::$debuggerPath. '/database.json'))
        {
            $fp = fopen(self::$debuggerPath. '/database.json', 'w');
            fwrite($fp, '{}');
            fclose($fp);
        }

        $this -> db = new writableJSON(self::$debuggerPath. '/database.json');
	}

	/**
	 * Load configuration file from path or array
	 *
	 * @throws Exception
	 * @param array|string $config Path to configuration file or array of key => values
	 * @param bool $merge (optional) Merge with existing config
	 * @author Damian Kęska <webnull.www@gmail.com>
	 * @return bool
	 */

	public function loadConfig($config, $merge=True)
	{
		if (!is_array($config) && is_string($config) && is_file($config) && is_readable($config))
		{
			$config = json_decode(file_get_contents($config), true);

			if (!is_array($config))
				throw new Exception('Cannot load configuration file from "' .$config. '". Exiting.', 1);
		}

		if ($merge and is_array($config))
			$config = array_merge($this -> config, $config);

		if (is_array($config))
		{
			$this -> config = $config;
			return true;
		}

		return false;
	}

	/**
	 * Log a message
	 *
	 * @param string $message Message content
	 * @param string $group Group/module/category name
	 * @param int $priorityLevel Priority level
	 * @author Damian Kęska <damian.keska@fingo.pl>
	 * @return array
	 */
	public function log($message, $group='default', $priorityLevel=1)
	{
		if (!$this->enabled || intval($priorityLevel) < $this->minPriority)
			return false;

		if ($this->printOutput)
			print($message."\n");

		$this->log[] = array(
			'message' => $message,
			'group' => $group,
			'priorityLevel' => $priorityLevel,
			'time' => microtime(true),
			'date' => time(),
		);
	}

	/**
	 * Send inline var_dump or print_r output to debugging window
	 *
	 * @param string $functionName Debugging function name
	 * @param array $backtraceLog Ouput of: array_shift(debug_backtrace())
	 * @param string $output Output
	 * @return null
	 */

	public function sendInlineDump($functionName, $backtraceLog, $output)
	{
		$this->inline[] = array(
			$functionName, $backtraceLog['file']. ':' .$backtraceLog['line'], $output,
		);
	}

	/**
	 * Start timer to count code exection time
	 *
	 * @author Damian Kęska <damian.keska@fingo.pl>
	 * @return null
	 */

	public function startTimer()
	{
		$this->timer = microtime(true);
	}

	/**
	 * A template engine hook that is executed on every template display().
	 * Adds template name to a list
	 *
	 * @param string $templateName Template name to add to list
	 * @author Damian Kęska <damian.keska@fingo.pl>
	 * @return null
	 */
	public function addTemplate($templateName)
	{
		$this->templateList[] = array(
			'name' => $templateName,
			'time' => substr((microtime(true)-$this->timer), 0, 8),
			'date' => microtime(true),
		);

		$this->timer = 0;
	}

	/**
	 * Get log in raw or formatted version
	 *
	 * @param bool $asString Return as formatted string or only just a raw array
	 * @param string $newLineSeparator Set newline separator, by default it's \n, but for HTML view can be "<br>" or even better - "\n<br>"
	 * @author Damian Kęska <damian.keska@fingo.pl>
	 * @return array
	 */
	public function getLog($asString=False, $newLineSeparator="\n")
	{
		$this->log = pHooks::execute('pantheraDebugger.getLog.log', $this->log);

		if($asString)
		{
			$text = '';

			$text = pHooks::execute('pantheraDebugger.getLog.text', $text);

			if ($this->log)
			{
				foreach ($this->log as $entryNumber => $logEntry)
				{
					$timing = '';

					if (isset($this->log[$entryNumber+1]))
					{
						$timing = substr($this->log[($entryNumber+1)]['time']-$logEntry['time'], 0, 5);
					}

					$text .= $logEntry['date']. ' [' .$logEntry['group']. '] ' .$timing. ' $' .$message.$newLineSeparator;
				}
			}

			return $text;
		}

		return $this->log;
	}

	public function arrayToNumericKeys($input)
	{
		$array = array();

		foreach ($input as $key => $value)
		{
			$array[] = array(
				$key, $value,
			);
		}

		return $array;
	}

	/**
	 * Load a debugger plugin (eg. framework/cms/project integration)
	 *
	 * @param string $className Plugin class name
	 * @author Damian Kęska <damian.keska@fingo.pl>
	 * @return bool
	 */

	public function loadPlugin($className)
	{
		if (!class_exists($className))
			return false;

		$this->plugins[$className] = new $className($this);
		return true;
	}

	/**
	 * Display a popup overlay on every page to show debugging informations
	 *
     * @param bool $onlyReturnData Only return data instead of displaying
	 * @author Damian Kęska <damian.keska@fingo.pl>
	 * @return null
	 */
	public function displayOverlay($onlyReturnData=False, $fileName=null)
	{
		if (!$this -> overlayEnabled or $this -> popupDisplayed)
			return false;
        
		$debuggerData = array();

		/**
		 * Load generated data from dump
		 */

		if (defined('InsidePantheraDebugger'))
		{
			if (isset($_GET['dumpID']) || $fileName)
			{
			    if (isset($_GET['dumpID']) && $_GET['dumpID'])
                    $fileName = $_GET['dumpID'];
                
				$dump = self::getContentDir('dumps/' .addslashes($fileName));
				$debuggerData = json_decode(file_get_contents($dump), true);
			}

		}

		if (!$debuggerData)
		{
			/**
			 * Generate data by application
			 */

			$debuggerData = array(
				'requestName' => $_SERVER['REQUEST_METHOD']. ' ' .$_SERVER['REQUEST_URI'],

				'data' => array(
					'inline' => array(
						'title' => 'Inline',
						'data' => $this -> inline,
						'template' => 'inline',
					),

					'request' => array(
						'title' => 'Request',
						'template' => 'request',
					),

					'log' => array(
						'title' => 'Logging',
						'data' => $this -> getLog(),
						'template' => 'logging',
					),

					'includes' => array(
						'title' => 'Included files',
						'data' => $this->arrayToNumericKeys(get_included_files()),
						'template' => 'includes',
					),

					'constants' => array(
						'title' => 'Constants',
						'data' => $this->arrayToNumericKeys(get_defined_constants()),
						'template' => 'constants',
					),
				),
			);

			$debuggerData = pHooks::execute('pantheraDebugger.customizeOverlay.data', $debuggerData);
		}

        $debuggerData['sessions'] = $this -> db -> get('sessions');
        
        if ($onlyReturnData)
            return $debuggerData;
        
		$this -> popupDisplayed = true;

        // load main template
		require_once self::getContentDir('templates/content.tpl.php');
        
		if (basename(__FILE__) == 'Debug.php' and defined('InsidePantheraDebugger'))
			overlayContent($debuggerData, $this);
		elseif ($this -> config['workMode'] == 'embedded') {
		    ob_start();
		    overlayContent($debuggerData, $this);
            $content = ob_get_clean();
            ob_end_clean();
            
			$popupContent = base64_encode($content);
			require self::getContentDir('templates/overlay.tpl.php');
		} else {
			$this -> saveDump($debuggerData, hash('md4', $debuggerData['requestName'].time()));
            $this -> cleanup();
            $this -> db -> save();
		}
	}

    /**
     * Request a data from specified tab
     * 
     * @param string $tabName Tab name
     * @param string $fileName Dump file name
     * @param string $path XPath to get froma array
     */

    public function ajaxRequestGetData($tabName, $fileName, $path='/')
    {
        $sessions = $this -> db -> get('sessions');
        
        if (isset($sessions[$fileName]))
        {
            $dumpsDir = self::getContentDir('dumps/');
            $data = json_decode(file_get_contents($dumpsDir. '/' .$fileName));
            $array = arrays::arrayXpathWalk($data['data'][$tabName], $path);
            
            print(json_encode($array));
            exit;
        }
    }
    
    /**
     * Ajax "getTab" request handler
     * 
     * @param string $tabName Tab name
     * @param string $fileName File name
     */
    
    public function ajaxRequestGetTab($tabName, $fileName)
    {
        $debuggerData = $this -> displayOverlay(true, $fileName);
        
        if (!isset($debuggerData['data'][$tabName]))
        {
            $tplFile = self::getContentDir('templates/' .$debuggerData['data'][$tabName]['template']. '.tpl.php');
            
            if (!$tplFile)
            {
                print(json_encode(array(
                    'status' => 'failed',
                    'message' => 'Cannot find template "' .$debuggerData['data'][$tabName]['template']. '.tpl.php"',
                )));
            }
            
            ob_start();
            require $tplFile;
            $content = ob_get_clean();
            ob_end_clean();
            
            print(json_encode(array(
                'status' => 'success',
                'data' => $content,
            )));
            
        } else {
            print(json_encode(array(
                'status' => 'failed',
                'message' => 'Tab "' .$tabName. '" does not exists',
            )));
        }
    }

	/**
	 * Clean up old dumps
	 *
	 * @author Damian Kęska <damian.keska@fingo.pl>
	 * @return null
	 */

	public function cleanup()
	{
		/**
		 * Clean up old files
		 */

		$dumpsDir = self::getContentDir('dumps/');
		$files = scandir($dumpsDir);

		if (!isset($this -> config['requestsMaxCount']))
			$this -> config['requestsMaxCount'] = 10;
            
		if ((count($files)-2) > $this -> config['requestsMaxCount'])
		{
		    $this -> log('Cleaning up dumps directory', 'pantheraDebugger', 1000);
		    $list = array();

			foreach ($files as $file)
			{
			    if ($file == '..' or $file == '.')
                    continue;
                
			    $list[] = array(
                    'fileName' => $dumpsDir. '/' .$file,
                    'modificationDate' => filemtime($dumpsDir. '/' .$file),
                );
			}
            
            usort($list, function ($item1, $item2) {
                return $item1['modificationDate'] - $item2['modificationDate'];
            });
            
            $diff = (count($files) - $this -> config['requestsMaxCount']);
            $sessions = $this -> db -> get('sessions');
            
            var_dump($sessions);
            
            for ($i=0; $i < $diff; $i++)
            {
                unlink($dumpsdir. '/' .$list[$i]['fileName']);
                unset($sessions[$list[$i]['fileName']]);
            }
            
            $this -> db -> set('sessions', $sessions);
            $this -> db -> save();
		}
	}
	/**
	 * Save dump data to file
	 *
	 * @param array $data Dump data
	 * @param string $fileName File name to write
	 * @author Damian Kęska <damian.keska@fingo.pl>
	 * @return bool
	 */

	public function saveDump($data, $fileName, $cleanup=False)
	{
		if ($cleanup)
			$this -> cleanup();
        
        unset($data['sessions']);
        
        $dumpsDir = self::getContentDir('dumps/');
		$fp = fopen($dumpsDir. '/' .$fileName, 'w');
		fwrite($fp, serialize($data));
		fclose($fp);
        
        $sessions = $this -> db -> get('sessions');
        $sessions[$fileName] = $data['requestName'];
        $this -> db -> set('sessions', $sessions);        $this -> db -> save();
        
		return is_file($dumpsDir. '/' .$fileName);
	}

	/**
	 * Get content dir (checks if file is in debugger directory or in application directory)
	 *
	 * @param string $path Input path
	 * @return string Output path
	 */

	public static function getContentDir($path)
	{
		if (file_exists($path))
			return $path;
		elseif (file_exists(self::$debuggerPath. '/' .$path))
			return self::$debuggerPath. '/' .$path;
	}
}

abstract class pantheraDebuggerPlugin
{
	protected $debugger = null;

	public function __construct($object)
	{
		$this->debugger = $object;
	}
}

function jsonDump($input)
{
	print('<script>console.log(atob("' .base64_encode(json_encode($input)). '"));</script>');
}

/**
 * Get instance of pantheraDebugger only if active
 *
 * @package Panthera\core\modules\debug
 * @author Damian Kęska
 * @return pantheraDebugger
 */

function getPantheraDebugger()
{
	if (class_exists('pantheraDebugger'))
	{
		$instance = pantheraDebugger::getInstance();

		if ($instance -> enabled && $instance -> overlayEnabled)
			return $instance;
	}
}

function r_print($arg)
{
	$bt = debug_backtrace();
	$return = print_r($arg, true);
	$dbg = getPantheraDebugger();

	if ($dbg)
		$dbg -> sendInlineDump('r_print', array_shift($bt), $return);

	return $return;
}

/**
 * Make a var_dump and return result
 *
 * @debug
 * @package Panthera\core\modules\debug
 * @author Damian Kęska
 * @return array
 */

function r_dump()
{
	$bt = debug_backtrace();

	ob_start();
	$var = func_get_args();
	call_user_func_array('var_dump', $var);

	$return = ob_get_clean();
	$dbg = getPantheraDebugger();

	if ($dbg)
		$dbg -> sendInlineDump('r_dump', array_shift($bt), $return);

	return $return;
}