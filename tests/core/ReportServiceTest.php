<?php
/**
 * This file is part of Rocketgraph service
 * <http://www.rocketgraph.com>
 */

namespace Tests\Core;

use RG\ReportService;
use RG\Test\ContainerAwareTestCase;

/**
 * Description of ReportServiceTest
 *
 * @author K.Christofilos <kostas.christofilos@rocketgraph.com>
 */
class ReportServiceTest extends ContainerAwareTestCase
{
    public function setUp()
    {
        parent::setUp();
        if (!is_dir(__DIR__ . '/../fixtures/reports/web/assets')) {
            $oldmask = umask(0);
            mkdir(__DIR__ . '/../fixtures/reports/web/assets', 0777, true);
            umask($oldmask);
        }
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->deleteContent(__DIR__ . '/../fixtures/reports/web');
        rmdir(__DIR__ . '/../fixtures/reports/web');
    }

    public function testClearRequest()
    {
        $_GET = ['key' => 'value'];
        $_POST = ['key' => 'value'];

        $reportService = $this->buildMockReportService('report1');
        $this->assertNull($reportService->getReport());
        $reportService->warmupReport();
    }

    public function testWarmupReport()
    {
        $reportService = $this->buildMockReportService('report2');
        $this->assertNull($reportService->getReport());
        try {
            $reportService->warmupReport();
        } catch (\Exception $ex) {
            $this->assertNull($reportService->getReport());
            $this->assertInstanceOf('RG\Exception\ReportNotFoundException', $ex);
        }

        $reportService = $this->buildMockReportService('report1');
        $this->assertNull($reportService->getReport());
        $reportService->warmupReport();

        $this->assertInstanceOf('RAM\BaseReport', $reportService->getReport());
    }

    public function testLinkFolders()
    {
        $reportService = $this->buildMockReportService('notWritable/report3');

        try {
            $reportService->warmupReport();
        } catch (\Exception $ex) {
            $this->assertInstanceOf('\RuntimeException', $ex);
        }

        $reportService = $this->buildMockReportService('report1');
        $reportService->setForceCopy(true);

        $reportService->warmupReport();
    }

    protected function buildMockReportService($report)
    {
        $connectorService = $this->getMockBuilder('RG\ConnectorService')
            ->disableOriginalConstructor()
            ->setMethods([
                'buildOpenConnector'
            ])
            ->getMock();
        $mockConnector = $this->getMockBuilder('RAM\Connectors\RemoteConnector')
            ->disableOriginalConstructor()
            ->getMock();

        $connectorService->expects($this->any())
            ->method('buildOpenConnector')
            ->willReturn($mockConnector);

        $storage = $this->container->get('storage_service');
        $dispatcher = $this->container->get('event_dispatcher');
        $logger = $this->container->get('logger');
        $templateHelper = $this->container->get('report_templating_helper');
        $srcPath = __DIR__ . '/../fixtures/reports/' . $report;
        $appPath = $this->container->getParameter('app_path');
        $reportService = new ReportService($connectorService, $storage, $dispatcher, $logger, $templateHelper, $srcPath, $appPath);

        return $reportService;
    }

    protected function deleteContent($path)
    {
        try {
            $iterator = new \DirectoryIterator($path);
            foreach ($iterator as $fileinfo) {
                if ($fileinfo->isDot()) continue;
                if ($fileinfo->isDir()) {
                    if ($fileinfo->isLink()) {
                        @unlink($fileinfo->getPathname());
                    } else {
                        if ($this->deleteContent($fileinfo->getPathname())) {
                            @rmdir($fileinfo->getPathname());
                        }
                    }
                }
                if ($fileinfo->isFile()) {
                    @unlink($fileinfo->getPathname());
                }
            }
        } catch (\Exception $ex) {
            return false;
        }

        return true;
    }
}