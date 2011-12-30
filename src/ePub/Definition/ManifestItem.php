<?php

/*
 * This file is part of the ePub Reader package
 *
 * (c) Justin Rainbow <justin.rainbow@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ePub\Definition;

class ManifestItem
{
	public $id;

	public $href;

	public $type;

	public $fallback;

	private $content;

	public function setContent($content)
	{
		$this->content = $content;
	}

	public function getContent()
	{
		if (is_callable($this->content)) {
			$func = $this->content;

			$this->content = $func();
		}

		return $this->content;
	}
}