<?php

declare(strict_types=1);

namespace Leoloso\GraphQLByPoPWPPlugin\Hooks;

use PoP\Engine\Hooks\AbstractHookSet;
use Leoloso\GraphQLByPoPWPPlugin\FieldResolvers\CPTFieldResolver;
use Leoloso\GraphQLByPoPWPPlugin\PostTypes\GraphQLCacheControlListPostType;
use Leoloso\GraphQLByPoPWPPlugin\PostTypes\GraphQLAccessControlListPostType;
use Leoloso\GraphQLByPoPWPPlugin\PostTypes\GraphQLSchemaConfigurationPostType;

class QueryHooks extends AbstractHookSet
{
    public const NON_EXISTING_ID = "non-existing";

    protected function init()
    {
        $this->hooksAPI->addAction(
            'CMSAPI:posts:query',
            [$this, 'convertPostsQuery'],
            10,
            2
        );
    }

    /**
     * Remove querying private CPTs
     *
     * @param array $query
     * @param array $options
     * @return array
     */
    public function convertPostsQuery($query, array $options): array
    {
        // Hooks must be active only when resolving the query into IDs,
        // and not when resolving IDs into object, since there we don't have `$options`
        if ($query['post_type']
            && !$options[CPTFieldResolver::QUERY_OPTION_ALLOW_QUERYING_PRIVATE_CPTS]
            && $options['return-type'] == \POP_RETURNTYPE_IDS
        ) {
            // These CPTs must not be queried from outside, since they contain private configuration data
            $query['post_type'] = array_diff(
                $query['post_type'],
                [
                    GraphQLAccessControlListPostType::POST_TYPE,
                    GraphQLCacheControlListPostType::POST_TYPE,
                    GraphQLSchemaConfigurationPostType::POST_TYPE,
                ]
            );
            // If there are no valid postTypes, then return no results
            // By not adding the post type, WordPress will fetch a "post"
            // Then, include a non-existing ID
            if (!$query['post_type']) {
                $query['include'] = self::NON_EXISTING_ID;
            }
        }
        return $query;
    }
}
