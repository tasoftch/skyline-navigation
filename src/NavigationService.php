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

use Skyline\Navigation\Exception\MissingNavigationLoaderException;
use Skyline\Navigation\Exception\MissingTemplateException;
use Skyline\Navigation\Loader\NavigationLoaderInterface;
use Skyline\Navigation\Loader\XMLLoader;
use Skyline\Render\Info\RenderInfoInterface;
use Skyline\Render\RenderInterface;
use Skyline\Render\Service\TemplateControllerInterface;
use Skyline\Render\Specification\Catalog;
use Skyline\Render\Template\TemplateInterface;
use TASoft\MenuService\MenuTool;
use TASoft\Service\Exception\UnknownServiceException;
use TASoft\Service\ServiceForwarderTrait;
use TASoft\Util\PathTool;

class NavigationService
{
    use ServiceForwarderTrait;
    const RENDER_NAVBAR_TEMPLATE_KEY = 'bar';


    /** @var NavigationLoaderInterface[] */
    private $navigationBars = [];

    /**
     * NavigatorService constructor.
     * @param $rootFiles
     */
    public function __construct($rootFiles)
    {
        foreach($rootFiles as $file) {
            $loader = $this->getLoaderForFile($file);
            if(!$loader) {
                $e = new MissingNavigationLoaderException("Missing loader for file %s", 0, NULL, basename($file));
                $e->setFilename($file);
                throw $e;
            }

            $this->navigationBars[ strtolower(PathTool::getPathName($file)) ] = $loader;
        }
    }

    /**
     * Create a specific loader for a file to load its contents into a navigation bar
     *
     * @param string $filename
     * @return NavigationLoaderInterface|null
     */
    protected function getLoaderForFile(string $filename): ?NavigationLoaderInterface {
        switch (strtolower(PathTool::getPathExtension($filename))) {
            case 'xml':
                return new XMLLoader($filename);

            default:
                return NULL;
        }
    }

    /**
     * Yields all navigation bars
     *
     * @return \Generator
     */
    public function yieldNavigationBars() {
        foreach($this->navigationBars as $key => $bar) {
            yield $key => $bar->getNavigationBar();
        }
    }

    /**
     * Loads and retrieves the navigation bar with a given id
     *
     * @param string $id
     * @return NavigationBarInterface|null
     */
    public function getNavigationBar(string $id): ?NavigationBarInterface {
        $bar = $this->navigationBars[$id] ?? NULL;
        if(!$bar)
            return NULL;

        return $bar->getNavigationBar();
    }

    /**
     * Finds a navigation bar by name
     *
     * @param string $name
     * @return NavigationBarInterface|null
     */
    public function findNavigationBar(string $name): ?NavigationBarInterface {
        /** @var NavigationBarInterface $bar */
        foreach($this->yieldNavigationBars() as $bar) {
            if($bar->getName() == $name)
                return $bar;
        }
        return NULL;
    }

    /**
     * Render a navigation using passed templates
     * See this classes constants RENDER_*_TEMPLATE_KEY for templates
     *
     * @param NavigationBarInterface $navigationBar
     * @param array $templates
     */
    public function renderNavigation(NavigationBarInterface $navigationBar, array $templates = []) {
        /** @var RenderInterface $rc */
        $rc = $this->render;

        if(method_exists($rc, "renderTemplate")) {
            $tmpCache = [];
            $getTemplate = function($key) use ($templates, &$tmpCache) {
                if(isset($tmpCache[$key]))
                    return $tmpCache[$key];
                if(isset($templates[$key]) && ($tmp = $templates[$key]) instanceof TemplateInterface)
                    return $tmpCache[$key] = $tmp;
                else {
                    $tmp = $this->loadTemplateForRenderKey($key);
                    if(!($tmp instanceof TemplateInterface)) {
                        $e = new MissingTemplateException("Can not render navigation. Template $key is missing");
                        $e->setTemplateKey($key);
                        throw $e;
                    }
                    return $tmpCache[$key] = $tmp;
                }
            };

            $bar = $getTemplate(self::RENDER_NAVBAR_TEMPLATE_KEY);

            $this->determineSelectedItems($navigationBar);

            $rc->renderTemplate($bar, [$navigationBar, $getTemplate]);
        } else {
            trigger_error(sprintf("The render %s does not support direct template render", get_class($rc)), E_USER_WARNING);
        }
    }

    /**
     * The default template loader searches for templates in following order:
     * 1.  A render info (If an action controller provides sub templates, look if one matches)
     * 2.  In category NavBar and then for matching names.
     *
     * @param string $key
     * @return TemplateInterface|null
     */
    protected function loadTemplateForRenderKey(string $key): ?TemplateInterface {
        // First, look into templates coming from render info if available.
        // The render info is provided by an action controller

        try {
            /** @var RenderInfoInterface $rinfo */
            $rinfo = $this->renderInfo;
            $subTemplates = $rinfo->get( RenderInfoInterface::INFO_SUB_TEMPLATES );
            if(isset($subTemplates[$key]))
                return $subTemplates[$key];
        } catch (UnknownServiceException $exception) {
            // Ignore and continue
        }

        /** @var TemplateControllerInterface $tc */
        $tc = $this->templateController;
        static $navBarTemplates;
        if(!$navBarTemplates)
            $navBarTemplates = $tc->findTemplates(new Catalog("NavBar"));

        /** @var TemplateInterface $template */
        foreach($navBarTemplates as $template) {
            if($template->getName() == $key)
                return $template;
        }
        return NULL;
    }

    protected function determineSelectedItems(NavigationBarInterface $navigationBar) {
        MenuTool::selectMenuItem($navigationBar->getMainMenu(), $_SERVER["REQUEST_URI"], MenuTool::SEL_OPTION_RECURSIVE | MenuTool::SEL_OPTION_BACKWARD);
    }
}