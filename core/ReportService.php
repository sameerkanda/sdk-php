<?php
/**
 * This file is part of Rocketgraph service
 * <http://www.rocketgraph.com>
 */

namespace RG;

use RAM\BaseReport;
use RAM\Interfaces\EventDispatcherInterface;
use RAM\Services\Logger;
use RAM\Services\Sentiment;
use RAM\Services\SpellingService;
use RAM\Services\Storage;
use RG\Exception\ReportNotFoundException;
use RG\RenderEngine\RenderEngine;
use RG\RenderEngine\ReportTemplatingHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of ReportService
 *
 * @author K.Christofilos <kostas.christofilos@rocketgraph.com>
 */
class ReportService
{
    /** @var ConnectorService */
    protected $connectorService;
    
    /** @var Storage */
    protected $storage;

    /** @var EventDispatcherInterface */
    protected $dispatcher;
    
    /** @var Logger */
    protected $logger;

    /** @var ReportTemplatingHelper */
    protected $templatingHelper;
    
    /** @var string */
    protected $srcPath;

    /** @var string */
    protected $appPath;

    /** @var BaseReport */
    protected $report;

    /** @var bool */
    protected $forceCopy;

    public function __construct(ConnectorService $connectorService, Storage $storage, 
                                EventDispatcherInterface $eventDispatcher, 
                                Logger $logger, ReportTemplatingHelper $templatingHelper, 
                                $srcPath, $appPath
    )
    {
        $this->connectorService = $connectorService;
        $this->storage = $storage;
        $this->dispatcher = $eventDispatcher;
        $this->logger = $logger;
        $this->templatingHelper = $templatingHelper;
        $this->srcPath = $srcPath;
        $this->appPath = $appPath;

        $this->forceCopy = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN';
    }

    /**
     * @param bool $force
     * 
     * @return ReportService
     */
    public function setForceCopy($force)
    {
        $this->forceCopy = (bool)$force;
        
        return $this;
    }
    
    /**
     * Warmup report before render
     */
    public function warmupReport()
    {
        $dirIterator = new \DirectoryIterator($this->srcPath);
        $this->report = null;
        foreach($dirIterator as $dir) {
            if (!$dir->isDot() && $dir->isFile()) {
                $reportPath = $dir->getPathname();
                if ($this->isValidReport($reportPath)) {
                    include_once $reportPath;
                    $class = $this->getReportClass($reportPath);
                    $this->report = new $class($this->dispatcher, $this->storage);
                    $logger = $this->logger;
                    $logger->setStorage($this->storage);
                    $this->report->setLogger($logger);
                    $this->setTemplateEngine($this->report);
                    $this->linkFolders($class);

                    break;
                }
            }
        }
        if (null === $this->report) {
            throw new ReportNotFoundException('No valid report found in src folder ('.$this->srcPath.')');
        }
        $this->setupConnectors();
        $this->setupParameters();
    }

    /**
     * Get report
     *
     * @return BaseReport|null
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * Setup request parameters
     */
    protected function setupParameters()
    {
        $request = Request::createFromGlobals();
        $this->report->setParams($request->query->all());
        $this->clearGetRequest();
    }

    /**
     * Setup report connectors
     */
    protected function setupConnectors()
    {
        $connectors = $this->connectorService->getConnectors();
        $this->report->setConnectors($connectors);

        $openConnector = $this->connectorService->buildOpenConnector();
        $this->report->setOpenConnector($openConnector);
        $this->report->setSentiment(new Sentiment());
        $this->report->setSpelling(new SpellingService());
    }

    /**
     * Get class name from report file
     *
     * @param string $reportPath
     * @return mixed
     */
    protected function getReportClass($reportPath)
    {
        $tokenizedReport = new Tokenizer($reportPath);
        return $tokenizedReport->getClass();
    }

    /**
     * Returns if report is a valid module
     *
     * @param string $fullClass
     * @param string $className
     * @return bool
     */
    protected function isValidReport($classPath)
    {
        $tokenized = new Tokenizer($classPath);
        return
            $tokenized->isExtended()
            && ($tokenized->getBaseClass() === 'RAM\BaseReport'
            || $tokenized->getBaseClass() === '\RAM\BaseReport');
    }

    /**
     * Setup template engine.
     *
     * @param BaseReport $report
     */
    protected function setTemplateEngine(BaseReport $report)
    {
        $basePath = $report->getReportPath();
        $templatePaths = [
            $basePath,
            $this->appPath.'/resources/views'
        ];
        $loader = new \Twig_Loader_Filesystem($templatePaths);
        $engine = new RenderEngine($loader, $report, $this->templatingHelper);
        $report->setRenderEngine($engine);
    }

    /**
     * Creates symbolic links for report assets.
     *
     * @param string     $className
     */
    protected function linkFolders($className)
    {
        $reportPublic = $this->srcPath
            .DIRECTORY_SEPARATOR.'public';

        if (is_dir($reportPublic)) {
            $assetsFolder = $this->srcPath
                .DIRECTORY_SEPARATOR.'..'
                .DIRECTORY_SEPARATOR.'web'
                .DIRECTORY_SEPARATOR.'assets';

            if (!is_writable($assetsFolder)) {
                throw new \RuntimeException("Folder '$assetsFolder' is not writable. Set the proper permissions.");
            }
            $symlinkFolder = $assetsFolder.DIRECTORY_SEPARATOR.$className;

            if ($this->forceCopy) {
                $this->copyFolder($reportPublic, $symlinkFolder);
            } else {
                @symlink($reportPublic, $symlinkFolder);
            }
        }
    }

    /**
     * Copy recursively a folder to another
     *
     * @param string $source
     * @param string $destination
     */
    protected function copyFolder($source, $destination)
    {
        if (!file_exists($destination)) {
            mkdir($destination, 0777, true);
        }
        $directory = new \DirectoryIterator($source);
        foreach($directory as $dir) {
            if (!$dir->isDot()) {
                $asset = $dir->getPathname();
                if (is_dir($asset)) {
                    $this->copyFolder($asset, $destination.DIRECTORY_SEPARATOR.basename($asset));
                } else {
                    $this->copyFile($asset, $destination);
                }
            }
        }
    }

    /**
     * Copy a file to another folder
     *
     * @param string $file
     * @param string $destinationFolder
     */
    protected function copyFile($file, $destinationFolder)
    {
        $destFile = $destinationFolder.DIRECTORY_SEPARATOR.basename($file);
        copy($file, $destFile);
    }

    /**
     * Clears the global $_GET, $_POST variables and $_SERVER['QUERY_STRING']
     */
    protected function clearGetRequest()
    {
        $_SERVER['QUERY_STRING'] = '';
        foreach ($_GET as $key => $value) {
            unset($_GET[$key]);
        }
        foreach ($_POST as $key => $value) {
            unset($_POST[$key]);
        }
    }
}