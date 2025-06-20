<?php

namespace Tests\Unit\Modules\Player\IndexCreation\Builder\Preparers;

use App\Modules\Player\Entities\PlayerEntity;
use App\Modules\Player\IndexCreation\Builder\Preparers\LayoutPreparer;
use App\Modules\Playlists\Helper\PlaylistMode;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LayoutPreparerTest extends TestCase
{
	private PlayerEntity&MockObject $playerEntityMock;
	private LayoutPreparer $preparer;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->playerEntityMock = $this->createMock(PlayerEntity::class);
		$this->preparer   = new LayoutPreparer($this->playerEntityMock);
	}

	#[Group('units')]
	public function testPrepareWithMultizone(): void
	{
		$this->playerEntityMock
			->method('getProperties')
			->willReturn(['width' => '1920', 'height' => '1080']);
		$this->playerEntityMock
			->method('getPlaylistMode')
			->willReturn(PlaylistMode::MULTIZONE->value);
		$this->playerEntityMock
			->method('getZones')
			->willReturn([
				'export_unit' => 'percent',
				'zones' => [
					'zone1' => [
						'zone_top' => 0,
						'zone_left' => 0,
						'zone_width' => 50,
						'zone_height' => 100,
						'zone_z-index' => 1,
						'zone_bgcolor' => '#FFF',
					],
				],
			]);

		$result = $this->preparer->prepare();

		$this->assertArrayHasKey('ROOT_LAYOUT_WIDTH', $result[0]);
		$this->assertEquals('1920', $result[0]['ROOT_LAYOUT_WIDTH']);
		$this->assertArrayHasKey('ROOT_LAYOUT_HEIGHT', $result[0]);
		$this->assertEquals('1080', $result[0]['ROOT_LAYOUT_HEIGHT']);
		$this->assertArrayHasKey('regions', $result[0]);
		$this->assertIsArray($result[0]['regions']);
		$this->assertCount(1, $result[0]['regions']);
		$this->assertEquals('0%', $result[0]['regions'][0]['REGION_LEFT']);
	}

	#[Group('units')]
	public function testPrepareWithMultizoneAsPixel(): void
	{
		$this->playerEntityMock
			->method('getProperties')
			->willReturn(['width' => '1920', 'height' => '1080']);
		$this->playerEntityMock
			->method('getPlaylistMode')
			->willReturn(PlaylistMode::MULTIZONE->value);
		$this->playerEntityMock
			->method('getZones')
			->willReturn([
				'export_unit' => 'pixel',
				'zones' => [
					'zone1' => [
						'zone_top' => 0,
						'zone_left' => 0,
						'zone_width' => 50,
						'zone_height' => 100,
						'zone_z-index' => 1,
						'zone_bgcolor' => '#FFF',
					],
				],
			]);

		$result = $this->preparer->prepare();

		$this->assertArrayHasKey('ROOT_LAYOUT_WIDTH', $result[0]);
		$this->assertEquals('1920', $result[0]['ROOT_LAYOUT_WIDTH']);
		$this->assertArrayHasKey('ROOT_LAYOUT_HEIGHT', $result[0]);
		$this->assertEquals('1080', $result[0]['ROOT_LAYOUT_HEIGHT']);
		$this->assertArrayHasKey('regions', $result[0]);
		$this->assertIsArray($result[0]['regions']);
		$this->assertCount(1, $result[0]['regions']);
		$this->assertEquals('0', $result[0]['regions'][0]['REGION_LEFT']);
	}


	#[Group('units')]
	public function testPrepareWithoutMultizone(): void
	{
		$this->playerEntityMock
			->method('getProperties')
			->willReturn(['width' => '1920', 'height' => '1080']);
		$this->playerEntityMock
			->method('getPlaylistMode')
			->willReturn(PlaylistMode::MASTER->value);

		$result = $this->preparer->prepare();

		$this->assertArrayHasKey('ROOT_LAYOUT_WIDTH', $result[0]);
		$this->assertEquals('1920', $result[0]['ROOT_LAYOUT_WIDTH']);
		$this->assertArrayHasKey('ROOT_LAYOUT_HEIGHT', $result[0]);
		$this->assertEquals('1080', $result[0]['ROOT_LAYOUT_HEIGHT']);
		$this->assertArrayHasKey('regions', $result[0]);
		$this->assertIsArray($result[0]['regions']);
		$this->assertCount(1, $result[0]['regions']);
		$this->assertEquals(0, $result[0]['regions'][0]['REGION_LEFT']);
	}
}
