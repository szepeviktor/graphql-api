<?php

declare(strict_types=1);

namespace GraphQLAPI\GraphQLAPI\Scripts;

use Error;
use GraphQLAPI\GraphQLAPI\General\GeneralUtils;
use GraphQLAPI\GraphQLAPI\General\EditorHelpers;

/**
 * Base class for a Gutenberg script.
 * The JS/CSS assets for each block is contained in folder {pluginDir}/scripts/{scriptName}, and follows
 * the architecture from @wordpress/create-block package
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-create-block/
 */
abstract class AbstractScript
{
    /**
     * Execute this function to initialize the script
     *
     * @return void
     */
    public function initialize(): void
    {
        \add_action('init', [$this, 'initScript']);
    }

    /**
     * Plugin dir
     *
     * @return string
     */
    abstract protected function getPluginDir(): string;
    /**
     * Plugin URL
     *
     * @return string
     */
    abstract protected function getPluginURL(): string;
    /**
     * Block name
     *
     * @return string
     */
    abstract protected function getScriptName(): string;

    // /**
    //  * Register CSS
    //  *
    //  * @return bool
    //  */
    // protected function registerScriptCSS(): bool
    // {
    //     return false;
    // }

    /**
     * Script localization name
     *
     * @return string
     */
    final protected function getScriptLocalizationName(): string
    {
        return GeneralUtils::dashesToCamelCase($this->getScriptName());
    }

    /**
     * Pass localized data to the block
     *
     * @return array
     */
    protected function getLocalizedData(): array
    {
        return [];
    }

    /**
     * URL to the script
     *
     * @return string
     */
    protected function getScriptDirURL(): string
    {
        return $this->getPluginURL() . '/scripts/' . $this->getScriptName() . '/';
    }

    /**
     * Where is the script stored
     *
     * @return string
     */
    protected function getScriptDir(): string
    {
        return $this->getPluginDir() . '/scripts/' . $this->getScriptName();
    }

    /**
     * Dependencies to load before the script
     *
     * @return array
     */
    protected function getScriptDependencies(): array
    {
        return [];
    }

    /**
     * Dependencies to load before the style
     *
     * @return array
     */
    protected function getStyleDependencies(): array
    {
        return [];
    }

    /**
     * Registers all script assets
     */
    public function initScript(): void
    {
        $dir = $this->getScriptDir();
        $scriptName = $this->getScriptName();

        $script_asset_path = "$dir/build/index.asset.php";
        if (!file_exists($script_asset_path)) {
            throw new Error(
                sprintf(
                    \__('You need to run `npm start` or `npm run build` for the "%s" script first.', 'graphql-api'),
                    $scriptName
                )
            );
        }

        $url = $this->getScriptDirURL();

        // Load the block scripts and styles
        $index_js     = 'build/index.js';
        $script_asset = require($script_asset_path);
        \wp_register_script(
            $scriptName,
            $url . $index_js,
            array_merge(
                $script_asset['dependencies'],
                $this->getScriptDependencies()
            ),
            $script_asset['version'],
            true
        );
        \wp_enqueue_script($scriptName);

        // /**
        //  * Register CSS file
        //  */
        // if ($this->registerScriptCSS()) {
        //     $style_css = 'style.css';
        //     \wp_register_style(
        //         $scriptName,
        //         $url . $style_css,
        //         $this->getStyleDependencies(),
        //         filemtime("$dir/$style_css")
        //     );
        //     \wp_enqueue_style($scriptName);
        // }

        /**
         * Localize the script with custom data
         * Execute on hook "wp_print_scripts" and not now,
         * because `getLocalizedData` might call EndpointHelpers::getAdminGraphQLEndpoint(),
         * which calls ScriptModelScriptConfiguration::namespaceTypesAndInterfaces(),
         * which is initialized during "wp"
         */
        \add_action('wp_print_scripts', function () use ($scriptName) {
            if ($localizedData = $this->getLocalizedData()) {
                \wp_localize_script(
                    $scriptName,
                    $this->getScriptLocalizationName(),
                    $localizedData
                );
            }
        });
    }
}
