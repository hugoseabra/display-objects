<?php
namespace Lcobucci\DisplayObjects\Core;

/**
 * Main class to create components
 *
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
abstract class UIComponent
{
	/**
	 * Stores the main dirname for the template's directory
	 *
	 * This will store the directory name to locate the templates on include path
	 *
	 * @var string
	 */
	private static $templatesDir = 'templates/';

	private $_namespaceSeparator = '\\';
	private $_templateExtension = '.phtml';

	/**
	 * Configures the template's directory
	 *
	 * @param string $dir
	 */
	public static function setTemplatesDir($dir)
	{
		if (substr($dir, -1, 1) != DIRECTORY_SEPARATOR) {
			$dir = $dir . DIRECTORY_SEPARATOR;
		}

		self::$templatesDir = $dir;
	}

	/**
	 * Returns the template's directory
	 *
	 * @return string
	 */
	protected function getTemplatesDir()
	{
		return self::$templatesDir;
	}

	/**
	 * Verify if the template exists on actual include path
	 *
	 * @param string $templateFile
	 * @return boolean
	 */
	protected function templateExists($templateFile)
	{
		return stream_resolve_include_path($templateFile);
	}

	/**
	 * Calculate the path of the template from the class name (according to PSR-0 autoloader)
	 *
	 * @param string $class
	 * @return string
	 */
	protected function getPath($class)
	{
		$fileName = '';
		$namespace = '';

		if (false !== ($lastNsPos = strripos($class, $this->_namespaceSeparator))) {
			$namespace = substr($class, 0, $lastNsPos);
			$class = substr($class, $lastNsPos + 1);

			$fileName = str_replace($this->_namespaceSeparator, DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;

			if (strpos($fileName, DIRECTORY_SEPARATOR) === 0) {
				$fileName = substr($fileName, 1);
			}
		}

		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $class) . $this->_templateExtension;

		return $fileName;
	}

	/**
	 * Returns the template file to be included
	 *
	 * @param string $class
	 * @return string
	 * @throws UIComponentNotFoundException
	 */
	protected function getFile($class)
	{
		$templateFile = $this->getTemplatesDir() . $this->getPath($class);

		if (!$this->templateExists($templateFile)) {
			throw new UIComponentNotFoundException('Template file not found for class ' . $class . '.');
		}

		return $templateFile;
	}

	/**
	 * Returns the template content (after proccessing)
	 *
	 * @return string
	 */
	public function show($class = null)
	{
		if (is_null($class)) {
			$class = get_class($this);
		}

		return $this->includeFile($this->getFile($class));
	}

	/**
	 * Get the template's content
	 *
	 * @param string $file
	 * @return string
	 */
	protected function includeFile($file)
	{
		ob_start();
		include $file;
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	/**
	 * Render the component
	 *
	 * @return string
	 */
	public function __toString()
	{
		try {
			return $this->show();
		} catch (\PDOException $e) {
			return '<pre>' . $e->getMessage() . '</pre>';
		} catch (\Exception $e) {
			return '<pre>' . $e . '</pre>';
		}
	}
}