module.exports = function( api ) {
	api.cache( true );

	return {
		presets: [
			'@wordpress/babel-preset-default',
		],
		plugins: [
		],
		env: {
			production: {
				plugins: [
					[
						'@wordpress/babel-plugin-makepot',
						{
							output: 'languages/ee-js.pot',
						},
					],
				],
			},
		},
	};
};
