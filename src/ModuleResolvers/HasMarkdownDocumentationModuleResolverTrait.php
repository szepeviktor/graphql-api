<?php

declare(strict_types=1);

namespace GraphQLAPI\GraphQLAPI\ModuleResolvers;

use Parsedown;

trait HasMarkdownDocumentationModuleResolverTrait
{
    /**
     * The name of the Markdown filename.
     * By default, it's the same as the slug
     *
     * @param string $module
     * @return string
     */
    public function getMarkdownFilename(string $module): ?string
    {
        return $this->getSlug($module) . '.md';
    }

    /**
     * Where the markdown file localized to the user's language is stored
     *
     * @param string $module
     * @return string
     */
    abstract public function getLocalizedMarkdownFileDir(string $module): string;

    /**
     * Where the default markdown file (for if the localized language is not available) is stored
     *
     * @param string $module
     * @return string
     */
    abstract public function getDefaultMarkdownFileDir(string $module): string;

    /**
     * Does the module have HTML Documentation?
     *
     * @param string $module
     * @return bool
     */
    public function hasDocumentation(string $module): bool
    {
        return !empty($this->getMarkdownFilename($module));
    }

    /**
     * Path URL to append to the local images referenced in the markdown file
     *
     * @param string $module
     * @return string|null
     */
    abstract protected function getDefaultMarkdownFileURL(string $module): string;

    /**
     * HTML Documentation for the module
     *
     * @param string $module
     * @return string|null
     */
    public function getDocumentation(string $module): ?string
    {
        if ($markdownFilename = $this->getMarkdownFilename($module)) {
            $localizedMarkdownFile = \trailingslashit($this->getLocalizedMarkdownFileDir($module)) . $markdownFilename;
            if (file_exists($localizedMarkdownFile)) {
                // First check if the localized version exists
                $markdownFile = $localizedMarkdownFile;
            } else {
                // Otherwise, use the default language version
                $markdownFile = \trailingslashit($this->getDefaultMarkdownFileDir($module)) . $markdownFilename;
                // Make sure this file exists
                if (!file_exists($markdownFile)) {
                    return sprintf(
                        '<p>%s</p>',
                        \__('Ops, the documentation for this module is not available', 'graphql-api')
                    );
                }
            }
            $markdownContents = file_get_contents($markdownFile);
            $htmlContents = (new Parsedown())->text($markdownContents);
            // Add the path to the images and anchors
            $defaultModulePathURL = $this->getDefaultMarkdownFileURL($module);
            $htmlContents = $this->appendPathURLToImages($defaultModulePathURL, $htmlContents);
            $htmlContents = $this->appendPathURLToAnchors($defaultModulePathURL, $htmlContents);
            return $htmlContents;
        }
        return null;
    }

    /**
     * Convert relative paths to absolute paths for image URLs
     *
     * @param string $pathURL
     * @param string $htmlContents
     * @return string
     */
    protected function appendPathURLToImages(string $pathURL, string $htmlContents): string
    {
        return $this->appendPathURLToElement('img', 'src', $pathURL, $htmlContents);
    }

    /**
     * Convert relative paths to absolute paths for image URLs
     *
     * @param string $pathURL
     * @param string $htmlContents
     * @return string
     */
    protected function appendPathURLToAnchors(string $pathURL, string $htmlContents): string
    {
        return $this->appendPathURLToElement('a', 'href', $pathURL, $htmlContents);
    }

    /**
     * Convert relative paths to absolute paths for elements
     *
     * @param string $tag
     * @param string $attr
     * @param string $pathURL
     * @param string $htmlContents
     * @return string
     */
    protected function appendPathURLToElement(string $tag, string $attr, string $pathURL, string $htmlContents): string
    {
        /**
         * $regex will become:
         * - /<img.*src="(.*?)".*?>/
         * - /<a.*href="(.*?)".*?>/
         */
        $regex = sprintf(
            '/<%s.*%s="(.*?)".*?>/',
            $tag,
            $attr
        );
        return preg_replace_callback(
            $regex,
            function ($matches) use ($pathURL) {
                // If the element has an absolute route, then no need
                if (
                    substr($matches[1], 0, strlen('http://')) == 'http://'
                    || substr($matches[1], 0, strlen('https://')) == 'https://'
                ) {
                    return $matches[0];
                }
                $elementURL = \trailingslashit($pathURL) . $matches[1];
                return str_replace(
                    "{$attr}=\"{$matches[1]}\"",
                    "{$attr}=\"{$elementURL}\"",
                    $matches[0]
                );
            },
            $htmlContents
        );
    }
}
