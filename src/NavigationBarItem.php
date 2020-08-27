<?php
/**
 * Copyright (c) 2019 TASoft Applications, Th. Abplanalp <info@tasoft.ch>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Skyline\Navigation;


use Skyline\Translation\TranslationManager;
use TASoft\MenuService\MenuItem;
use TASoft\Service\ServiceManager;

class NavigationBarItem extends MenuItem
{
    private $separatorItem = false;

    public static $screenReaderCurrentMarker = '<span class="sr-only">(current)</span>';

    public function __construct(string $identifier, string $title = "")
	{
		if($title && class_exists( TranslationManager::class )) {
			/** @var TranslationManager $tm */
			if($tm = ServiceManager::generalServiceManager()->get( TranslationManager::SERVICE_NAME )) {
				$t = $tm->translateGlobal($identifier, 'menu');
				if($t != $identifier)
					$title = $t;
			}

		}
		parent::__construct($identifier, $title);
	}

	/**
     * @return bool
     */
    public function isSeparatorItem(): bool
    {
        return $this->separatorItem;
    }

	/**
	 * @param bool $separatorItem
	 * @return NavigationBarItem
	 */
    public function setSeparatorItem(bool $separatorItem)
    {
        $this->separatorItem = $separatorItem;
        return $this;
    }
}