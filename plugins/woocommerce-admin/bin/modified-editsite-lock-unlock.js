/**
 * External dependencies
 */
import { __dangerousOptInToUnstableAPIsOnlyForCoreModules } from '@wordpress/private-apis';

// Workaround for Gutenberg private API consent string differences between WP 6.3 and 6.4+
// The modified version checks for the WP version and replaces the consent string with the correct one.
// This can be removed once we drop support for WP 6.3 in the "Customize Your Store" task.
// See this PR for details: https://github.com/woocommerce/woocommerce/pull/40884

const wordPressConsentString = {
	6.4: 'I know using unstable features means my plugin or theme will inevitably break on the next WordPress release.',
	6.5: 'I know using unstable features means my theme or plugin will inevitably break in the next version of WordPress.',
	6.6: 'I acknowledge private features are not for use in themes or plugins and doing so will break in the next version of WordPress.',
};

function optInToUnstableAPIs() {
	let error;
	for ( const optInString of Object.values( wordPressConsentString ) ) {
		try {
			return __dangerousOptInToUnstableAPIsOnlyForCoreModules(
				optInString,
				'@wordpress/edit-site'
			);
		} catch ( anError ) {
			error = anError;
		}
	}

	throw error;
}

export const { lock, unlock } = optInToUnstableAPIs();
//# sourceMappingURL=lock-unlock.js.map
