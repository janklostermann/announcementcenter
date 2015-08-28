<?php

/**
 * ownCloud - AnnouncementCenter App
 *
 * @author Joas Schilling
 * @copyright 2014 Joas Schilling nickvergessen@owncloud.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\AnnouncementCenter\Tests;

class AppTest extends TestCase {
	/** @var \OCP\INavigationManager|\PHPUnit_Framework_MockObject_MockObject */
	protected $navigationManager;
	/** @var \OCP\IURLGenerator|\PHPUnit_Framework_MockObject_MockObject */
	protected $urlGenerator;
	/** @var \OCP\IL10N|\PHPUnit_Framework_MockObject_MockObject */
	protected $language;
	/** @var \OCP\L10N\IFactory|\PHPUnit_Framework_MockObject_MockObject */
	protected $languageFactory;
	/** @var \OCP\Activity\IManager|\PHPUnit_Framework_MockObject_MockObject */
	protected $activityManager;

	protected function setUp() {
		parent::setUp();

		$this->navigationManager = $this->getMockBuilder('OCP\INavigationManager')
			->disableOriginalConstructor()
			->getMock();
		$this->urlGenerator = $this->getMockBuilder('OCP\IURLGenerator')
			->disableOriginalConstructor()
			->getMock();
		$this->languageFactory = $this->getMockBuilder('OCP\L10N\IFactory')
			->disableOriginalConstructor()
			->getMock();
		$this->activityManager = $this->getMockBuilder('OCP\Activity\IManager')
			->disableOriginalConstructor()
			->getMock();
		$this->language = $this->getMockBuilder('OCP\IL10N')
			->disableOriginalConstructor()
			->getMock();
		$this->language->expects($this->any())
			->method('t')
			->willReturnCallback(function($string, $args) {
				return vsprintf($string, $args);
			});

		$this->overwriteService('NavigationManager', $this->navigationManager);
		$this->overwriteService('ActivityManager', $this->activityManager);
		$this->overwriteService('URLGenerator', $this->urlGenerator);
		$this->overwriteService('L10NFactory', $this->languageFactory);
	}

	protected function tearDown() {
		$this->restoreService('NavigationManager');
		$this->restoreService('ActivityManager');
		$this->restoreService('URLGenerator');
		$this->restoreService('L10NFactory');

		parent::tearDown();
	}

	public function testAppNavigation() {
		$this->navigationManager->expects($this->once())
			->method('add')
			->willReturnCallback(function($closure) {
				$this->assertInstanceOf('\Closure', $closure);
				$navigation = $closure();
				$this->assertInternalType('array', $navigation);
				$this->assertCount(5, $navigation);
				$this->assertSame([
					'id' => 'announcementcenter',
					'order' => 10,
					'href' => '/apps/announcementcenter/announcement',
					'icon' => '/apps/announcementcenter/img/app.svg',
					'name' => 'Announcements',
					], $navigation);
			});
		$this->urlGenerator->expects($this->once())
			->method('linkToRoute')
			->with('announcementcenter.page.index')
			->willReturn('/apps/announcementcenter/announcement');
		$this->urlGenerator->expects($this->once())
			->method('imagePath')
			->with('announcementcenter', 'app.svg')
			->willReturn('/apps/announcementcenter/img/app.svg');
		$this->languageFactory->expects($this->once())
			->method('get')
			->with('announcementcenter')
			->willReturn($this->language);

		include(__DIR__ . '/../../appinfo/app.php');
	}

	public function testAppActivity() {
		$this->activityManager->expects($this->once())
			->method('registerExtension')
			->willReturnCallback(function($closure) {
				$this->assertInstanceOf('\Closure', $closure);
				$navigation = $closure();
				$this->assertInstanceOf('\OCA\AnnouncementCenter\ActivityExtension', $navigation);
			});

		include(__DIR__ . '/../../appinfo/app.php');
	}
}