/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/#registering-a-block
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * Application imports
 */
import EditBlock from './EditBlock.js';

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/#registering-a-block
 */
registerBlockType( 'graphql-by-pop/graphiql', {
	/**
	 * This is the display title for your block, which can be translated with `i18n` functions.
	 * The block inserter will show this name.
	 */
	title: __( 'GraphiQL', 'graphql-by-pop' ),

	/**
	 * This is a short description for your block, can be translated with `i18n` functions.
	 * It will be shown in the Block Tab in the Settings Sidebar.
	 */
	description: __(
		'GraphiQL client to query the GraphQL server.',
		'graphql-by-pop'
	),

	/**
	 * Blocks are grouped into categories to help users browse and discover them.
	 * The categories provided by core are `common`, `embed`, `formatting`, `layout` and `widgets`.
	 */
	category: 'widgets',

	/**
	 * An icon property should be specified to make it easier to identify a block.
	 * These can be any of WordPress’ Dashicons, or a custom svg element.
	 */
	icon: <svg xmlns="http://www.w3.org/2000/svg" height="64" width="64" viewBox="0 0 29.999 30" fill="#e10098"><path d="M4.08 22.864l-1.1-.636L15.248.98l1.1.636z"/><path d="M2.727 20.53h24.538v1.272H2.727z"/><path d="M15.486 28.332L3.213 21.246l.636-1.1 12.273 7.086zm10.662-18.47L13.874 2.777l.636-1.1 12.273 7.086z"/><path d="M3.852 9.858l-.636-1.1L15.5 1.67l.636 1.1z"/><path d="M25.922 22.864l-12.27-21.25 1.1-.636 12.27 21.25zM3.7 7.914h1.272v14.172H3.7zm21.328 0H26.3v14.172h-1.272z"/><path d="M15.27 27.793l-.555-.962 10.675-6.163.555.962z"/><path d="M27.985 22.5a2.68 2.68 0 0 1-3.654.981 2.68 2.68 0 0 1-.981-3.654 2.68 2.68 0 0 1 3.654-.981c1.287.743 1.724 2.375.98 3.654M6.642 10.174a2.68 2.68 0 0 1-3.654.981A2.68 2.68 0 0 1 2.007 7.5a2.68 2.68 0 0 1 3.654-.981 2.68 2.68 0 0 1 .981 3.654M2.015 22.5a2.68 2.68 0 0 1 .981-3.654 2.68 2.68 0 0 1 3.654.981 2.68 2.68 0 0 1-.981 3.654c-1.287.735-2.92.3-3.654-.98m21.343-12.326a2.68 2.68 0 0 1 .981-3.654 2.68 2.68 0 0 1 3.654.981 2.68 2.68 0 0 1-.981 3.654 2.68 2.68 0 0 1-3.654-.981M15 30a2.674 2.674 0 1 1 2.674-2.673A2.68 2.68 0 0 1 15 30m0-24.652a2.67 2.67 0 0 1-2.674-2.674 2.67 2.67 0 1 1 5.347 0A2.67 2.67 0 0 1 15 5.347"/></svg>,

	/**
	 * Block default attributes.
	 */
	attributes: {
		/**
		 * If not set as an empty string by default, when first initializing a block the query/variables would be undefined
		 * In that case, initialize the query with an initial value, and do not let GraphiQL get the initial value from the cache
		 * This is because of a potential bug: the state is not saved until executing `onEditQuery` or `onEditVariables`, meaning that the user needs to edit the inputs
		 * However, if the previous input is good (eg: the new query uses the same variables as the last query) and the user never edits it again, the state will not be saved
		 * To force the user to always edit the query, and thus save the state, then initialize the inputs to some default empty value, which is not useful as it is to the query
		 */
		query: {
			type: 'string',
			default: '# Welcome to GraphiQL\n#\n# GraphiQL is an in-browser tool for writing, validating, and\n# testing GraphQL queries.\n#\n# Type queries into this side of the screen, and you will see intelligent\n# typeaheads aware of the current GraphQL type schema and live syntax and\n# validation errors highlighted within the text.\n#\n# GraphQL queries typically start with a \u0022{\u0022 character. Lines that starts\n# with a # are ignored.\n#\n# An example GraphQL query might look like:\n#\n#     {\n#       field(arg: \u0022value\u0022) {\n#         subField\n#       }\n#     }\n#\n# Keyboard shortcuts:\n#\n#  Prettify Query:  Shift-Ctrl-P (or press the prettify button above)\n#\n#     Merge Query:  Shift-Ctrl-M (or press the merge button above)\n#\n#       Run Query:  Ctrl-Enter (or press the play button above)\n#\n#   Auto Complete:  Ctrl-Space (or just start typing)\n#\n\n'
		},
		variables: {
			type: 'string',
			default: ''
		},
		// Make it wide alignment by default
		align: {
			type: 'string',
			default: 'wide'
		},
	},

	/**
	 * Example for the Inspector Help Panel
	 */
	example: {
		attributes: {
			query: "query {\n  posts(limit:3) {\n    id\n    title\n  }\n}"
		}
	},

	/**
	 * Optional block extended support features.
	 */
	supports: {
		// Alignment options
		align: ['center', 'wide', 'full'],
		// Remove the support for the custom className.
		customClassName: false,
		// // Remove support for an HTML mode.
		// html: false,
		// // Only insert block through a template
		// inserter: false,
		// // Only one per CPT is needed
		// multiple: false,
		// // Don't allow the block to be converted into a reusable block.
		// reusable: false
	},

	/**
	 * The edit function describes the structure of your block in the context of the editor.
	 * This represents what the editor will render when the block is used.
	 *
	 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
	 *
	 * @param {Object} [props] Properties passed from the editor.
	 *
	 * @return {WPElement} Element to render.
	 */
	edit: EditBlock,

	/**
	 * The save function defines the way in which the different attributes should be combined
	 * into the final markup, which is then serialized by the block editor into `post_content`.
	 *
	 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#save
	 *
	 * @return {WPElement} Element to render.
	 */
	save() {
		return null;
	},
} );
