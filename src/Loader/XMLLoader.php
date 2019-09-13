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

namespace Skyline\Navigation\Loader;


use Skyline\Navigation\Generator\GeneratorInterface;
use TASoft\MenuService\Action\LiteralStringAction;
use TASoft\MenuService\Action\RegexStringAction;
use TASoft\MenuService\Menu;
use TASoft\MenuService\MenuItem;
use TASoft\Util\PathTool;
use Skyline\Navigation\NavigationBarInterface;
use Skyline\Navigation\NavigationBar;
use Skyline\Navigation\BrandItem;
use Skyline\Navigation\NavigationBarItem;

class XMLLoader extends AbstractFileNavigationLoader
{
    function loadInstance(): ?NavigationBarInterface
    {
        $xml = @simplexml_load_file($this->getFilename());
        if(!$xml) {
            $err = error_get_last();
            // TODO: Error handling
            return NULL;
        }

        $nb = new NavigationBar(PathTool::getPathName($this->getFilename()));
        $nb->setName($xml["name"]);

        $menu = new Menu($nb->getIdentifier());

        if($brand = $xml->brand) {
            if($name = $brand["name"]) {
                $url = $brand["URL"];
                $icon = $brand["icon"];

                $nb->setBrandItem(new BrandItem($name, $url, $icon));
            }
        }

        if($cssClasses = $xml->css) {
            $classes = [];
            foreach($cssClasses->class as $class) {
                $classes[] = (string) $class;
            }
            $nb->setCSSClasses($classes);
        }

        $readMenu = function($xmlElement, Menu $parentMenu) use (&$readMenu) {
            foreach($xmlElement->item as $item) {
                if($item["separator"] == 'YES') {
                    $mitem = new NavigationBarItem(uniqid("sep_"));
                    $mitem->setSeparatorItem(true);
                    $parentMenu->addItem($mitem);

                    continue;
                }
                $id = (string)$item["id"];
                $title = (string)$item->title;
                if($id && $title) {
                    $mitem = new NavigationBarItem($id, $title);
                    $this->readXMLIntoMenuItem($item, $mitem);

                    $parentMenu->addItem($mitem);

                    if($subm = $item->submenu) {
                        $sm = NULL;

                        if($subm["generator"] && class_exists($genClass = (string) $subm["generator"])) {
                            $gen = new $genClass();
                            if($gen instanceof GeneratorInterface) {
                                $sm = $gen->getGeneratedMenu();
                                $mitem->setSubmenu($sm);
                            }
                        } else {
                            $sm = new Menu("$id-submenu");
                            $readMenu($subm, $sm);
                            $mitem->setSubmenu($sm);
                        }
                    }
                }
            }
        };

        $readMenu($xml, $menu);
        $nb->setMainMenu($menu);
        return $nb;
    }

    protected function readXMLIntoMenuItem($xmlElement, MenuItem $menuItem) {
        if($action = $xmlElement->action) {
            if($regex = $action["regex"] ?? "") {
                $act = new RegexStringAction((string)$action, (string) $regex);
            } else {
                $act = new LiteralStringAction((string)$action);
            }

            $menuItem->setAction($act);
        }
    }
}