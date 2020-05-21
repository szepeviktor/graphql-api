<?php

declare(strict_types=1);

namespace Leoloso\GraphQLByPoPWPPlugin\Admin\MenuPages;

/**
 * Menu Page
 */
interface MenuPageInterface
{
    /**
     * Print the menu page HTML content
     *
     * @return void
     */
    public function print(): void;
}