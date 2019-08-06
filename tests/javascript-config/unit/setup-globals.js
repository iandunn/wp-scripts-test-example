/**
 * Setup globals used in various tests
 */

// Set up `wp.*` aliases.  Doing this because any tests importing wp stuff will
// likely run into this.
global.wp = {
	shortcode: {
		next() {},
		regexp: jest.fn(),
	},
};

Object.defineProperty( global.wp, 'element', {
	get: () => require( '@wordpress/element' ),
} );
Object.defineProperty( global.wp, 'blocks', {
	get: () => require( '@wordpress/blocks' ),
} );

// this just saves having to import React in all our files (so JSX is
// transformed)for tests to work.  For builds, webpack is configured to provide
// the React module to any files needing it.
global.React = require( 'react' );
