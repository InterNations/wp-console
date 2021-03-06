<?php

/**
 * @file
 * Contains \WP\Console\Generator\SidebarGenerator.
 */

namespace WP\Console\Generator;

use WP\Console\Extension\Manager;
use WP\Console\Core\Generator\Generator;
use WP\Console\Utils\Site;

/**
 * Class SidebarGenerator
 *
 * @package WP\Console\Generator
 */
class SidebarGenerator extends Generator
{
    /**
     * @var Manager
     */
    protected $extensionManager;

    /**
     * SidebarGenerator constructor.
     *
     * @param Manager $extensionManager
     */
    public function __construct(
        Manager $extensionManager
    ) {
        $this->extensionManager = $extensionManager;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $parameters, Site $site)
    {
        $theme = $parameters['theme'];

        $discoverThemes = $this->extensionManager->discoverThemes()->showActivated()->showDeactivated()->getList();
        $extensions = array_combine(array_keys($discoverThemes), array_column($discoverThemes, 'Name'));
        $themeName = array_search($theme, $extensions);

        $class_name_sidebar = 'admin/partials/Sidebar/'.$themeName.'Sidebar.php';
        $themeFile = $this->extensionManager->getTheme($theme)->getPathname();
        $dir = $this->extensionManager->getTheme($theme)->getPath().'/'.$class_name_sidebar;

        $parameters = array_merge(
            $parameters, [
            "class_name_sidebar_path" => $class_name_sidebar,
            "file_exists" => file_exists($themeFile.'/functions.php'),
            "admin_file_exists" => file_exists($dir)
            ]
        );

        $site->loadLegacyFile($dir);

        if (function_exists($parameters['function_name'])) {
            throw new \RuntimeException(
                sprintf(
                    'Unable to generate the sidebar , The function name already exist at "%s"',
                    realpath($dir)
                )
            );
        }

        $this->renderFile(
            'theme/functions.php.twig',
            $themeFile.'/functions.php',
            $parameters,
            FILE_APPEND
        );

        $this->renderFile(
            'theme/src/Sidebar/sidebar.php.twig',
            $dir,
            $parameters,
            FILE_APPEND
        );
    }
}
