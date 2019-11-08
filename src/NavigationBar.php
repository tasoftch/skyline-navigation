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

use TASoft\MenuService\MenuInterface;

class NavigationBar implements NavigationBarInterface
{
    /** @var string */
    private $name;
    /** @var string */
    private $identifier;

    /** @var MenuInterface */
    private $mainMenu;

    private $CSSClasses = ['navbar-light', 'bg-light'];

    /** @var BrandItem|null */
    private $brandItem;

    /**
     * @var string
     */
    private $collapseSize = 'md';

    /**
     * NavigationBar constructor.
     * @param string $identifier
     */
    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return MenuInterface
     */
    public function getMainMenu(): MenuInterface
    {
        return $this->mainMenu;
    }

    /**
     * @param MenuInterface $mainMenu
     */
    public function setMainMenu(MenuInterface $mainMenu): void
    {
        $this->mainMenu = $mainMenu;
    }

    /**
     * @return array
     */
    public function getCSSClasses(): array
    {
        return $this->CSSClasses;
    }

    /**
     * @param array $CSSClasses
     */
    public function setCSSClasses(array $CSSClasses): void
    {
        $this->CSSClasses = $CSSClasses;
    }

    /**
     * @return string
     */
    public function getCollapseSize(): string
    {
        return $this->collapseSize;
    }

    /**
     * @param string $collapseSize
     */
    public function setCollapseSize(string $collapseSize): void
    {
        $this->collapseSize = $collapseSize;
    }

    /**
     * @return BrandItem|null
     */
    public function getBrandItem(): ?BrandItem
    {
        return $this->brandItem;
    }

    /**
     * @param BrandItem|null $brandItem
     */
    public function setBrandItem(?BrandItem $brandItem): void
    {
        $this->brandItem = $brandItem;
    }
}