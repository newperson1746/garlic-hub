<?php

namespace Tests\Unit\Modules\Playlists\Helper\Settings;

use App\Framework\Core\Sanitizer;
use App\Framework\Core\Session;
use App\Framework\Exceptions\ModuleException;
use App\Modules\Playlists\Helper\Settings\Parameters;
use Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

class ParametersTest extends TestCase
{
	private Parameters $parameters;

	/**
	 * @throws Exception|\PHPUnit\Framework\MockObject\Exception
	 */
	protected function setUp(): void
	{
		$sanitizerMock = $this->createMock(Sanitizer::class);
		$sessionMock = $this->createMock(Session::class);

		$this->parameters    = new Parameters($sanitizerMock, $sessionMock);
	}

	#[Group('units')]
	public function testConstructor(): void
	{
		$this->assertCount(2, $this->parameters->getCurrentParameters());
		$this->assertSame('playlists', $this->parameters->getModuleName());
		$this->assertInstanceOf(Parameters::class, $this->parameters);
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testAddPlaylistMode(): void
	{
		$this->assertFalse($this->parameters->hasParameter(Parameters::PARAMETER_PLAYLIST_MODE));
		$this->parameters->addPlaylistMode();
		$this->assertCount(3, $this->parameters->getCurrentParameters());
		$this->assertTrue($this->parameters->hasParameter(Parameters::PARAMETER_PLAYLIST_MODE));
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testAddPlaylistId(): void
	{
		$this->assertFalse($this->parameters->hasParameter(Parameters::PARAMETER_PLAYLIST_ID));
		$this->parameters->addPlaylistId();
		$this->assertCount(3, $this->parameters->getCurrentParameters());
		$this->assertTrue($this->parameters->hasParameter(Parameters::PARAMETER_PLAYLIST_ID));
	}

	/**
	 * @throws ModuleException
	 */
	#[Group('units')]
	public function testAddTimeLimit(): void
	{
		$this->assertFalse($this->parameters->hasParameter(Parameters::PARAMETER_TIME_LIMIT));
		$this->parameters->addTimeLimit();
		$this->assertCount(3, $this->parameters->getCurrentParameters());
		$this->asserttrue($this->parameters->hasParameter(Parameters::PARAMETER_TIME_LIMIT));
	}

}
