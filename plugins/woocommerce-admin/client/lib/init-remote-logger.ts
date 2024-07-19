/**
 * External dependencies
 */
import { init } from '@woocommerce/remote-logger';

init( {
	rateLimitInterval: 60000, // 1 minute
} );

// setTimeout( () => {
// 	throw new Error( 'Test error' );
// }, 1000 );
