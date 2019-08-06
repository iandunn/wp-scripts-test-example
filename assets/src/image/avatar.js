/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export class AvatarImage extends Component {
	render() {
		const {
			avatarUrl,
			avatarClass,
			avatarHeight,
			avatarWidth,
			avatarAltText,
		} = this.props;

		const style = {
			height: avatarHeight,
			width: avatarWidth,
		};
		return avatarUrl ? (
			<div className={ avatarClass + '-image-wrap-div' }>
				<img
					className={ avatarClass + '-avatar-img avatar' }
					src={ avatarUrl }
					style={ style }
					alt={ avatarAltText }
				/>
			</div>
		) : (
			null
		);
	}
}
