/**
 * External dependencies
 */
import debugFactory from 'debug';
import { getSetting } from '@woocommerce/settings';

const debug = debugFactory( 'wc:remote-logger' );

export type RemoteLoggerConfig = {
	rateLimitInterval: number; // in milliseconds
};

export type LogData = {
	/**
	 * The message to log.
	 */
	message: string;
	/**
	 * A feature slug. Defaults to 'woocommerce_core'. Need to be added to the features list in API before using.
	 */
	feature?: string;
	/**
	 * The severity of the log.
	 */
	severity?:
		| 'emergency'
		| 'alert'
		| 'critical'
		| 'error'
		| 'warning'
		| 'notice'
		| 'info'
		| 'debug';
	/**
	 * Extra data to include in the log.
	 */
	extra?: unknown;
	/**
	 * Tags to add to the log.
	 */
	tags?: string[];
	/**
	 * Properties to add to the log. Unlike `extra`, it won't be serialized to a string.
	 */
	properties?: Record< string, unknown >;
};

const REMOTE_LOG_LOCAL_STORAGE_KEY = 'woocommerce_last_remote_log_time';

const DEFAULT_LOG_ATTRIBUTES = {
	feature: 'woocommerce_core',
	host: window.location.hostname,
	tags: [ 'woocommerce', 'js' ],
	properties: {
		wp_version: getSetting( 'wpVersion' ),
		wc_version: getSetting( 'wcVersion' ),
	},
};

class RemoteLogger {
	private config: RemoteLoggerConfig;
	private lastLogTime = 0;

	public constructor( config: RemoteLoggerConfig ) {
		this.config = config;
		this.lastLogTime = parseInt(
			localStorage.getItem( REMOTE_LOG_LOCAL_STORAGE_KEY ) || '0',
			10
		);
	}

	/**
	 * Sends a log entry to the remote API.
	 *
	 * @param logData - The log data to be sent.
	 */
	private async sendLogToAPI( logData: LogData ): Promise< void > {
		const body = new window.FormData();
		const params = {
			...logData,
		};
		body.append( 'params', JSON.stringify( params ) );

		debug( 'Sending log to API:', params );

		try {
			await window.fetch(
				'https://public-api.wordpress.com/rest/v1.1/logstash',
				{
					method: 'POST',
					body,
				}
			);
		} catch ( error ) {
			// eslint-disable-next-line no-console
			console.error( 'Failed to send log to API:', error );
		}
	}

	/**
	 * Sends an error to the remote API.
	 *
	 * @param errorData         - The error data to be sent.
	 * @param errorData.message - The error message.
	 * @param errorData.trace   - The error trace.
	 */
	private async sendErrorToAPI( errorData: {
		message: string;
		trace?: string;
	} ): Promise< void > {
		const body = new window.FormData();
		const error = {
			...DEFAULT_LOG_ATTRIBUTES,
			message: errorData.message,
			trace: errorData.trace,
			severity: 'critical',
			tags: [ ...DEFAULT_LOG_ATTRIBUTES.tags, 'js-unhandled-error' ],
		};
		body.append( 'error', JSON.stringify( error ) );

		debug( 'Sending error to API:', error );

		try {
			await window.fetch(
				'https://public-api.wordpress.com/rest/v1.1/js-error',
				{
					method: 'POST',
					body,
				}
			);
		} catch ( _error: unknown ) {
			// eslint-disable-next-line no-console
			console.error( 'Failed to send error to API:', _error );
		}
	}

	/**
	 * Logs a message or error, respecting rate limiting.
	 *
	 * @param severity  - The severity of the log.
	 * @param message   - The message to log.
	 * @param extraData - Optional additional data to include in the log.
	 */
	public log(
		severity: LogData[ 'severity' ],
		message: string,
		extraData?: Partial< LogData >
	): void {
		const currentTime = Date.now();
		if ( currentTime - this.lastLogTime < this.config.rateLimitInterval ) {
			debug( 'Rate limit reached. Skipping log:', {
				severity,
				message,
				extraData,
			} );
			return;
		}

		this.lastLogTime = currentTime;
		localStorage.setItem(
			REMOTE_LOG_LOCAL_STORAGE_KEY,
			this.lastLogTime.toString()
		);

		const logData: LogData = {
			...DEFAULT_LOG_ATTRIBUTES,
			message,
			severity,
			...extraData,
			tags: [
				...DEFAULT_LOG_ATTRIBUTES.tags,
				...( extraData?.tags || [] ),
			],
			properties: {
				...DEFAULT_LOG_ATTRIBUTES.properties,
				...extraData?.properties,
			},
		};

		this.sendLogToAPI( logData );
	}

	/**
	 * Initializes error event listeners for automatic error logging.
	 */
	public initializeErrorHandlers(): void {
		window.addEventListener( 'error', ( event ) => {
			debug( 'Caught error event:', event );

			this.sendErrorToAPI( {
				message: event.message,
				trace: event.error?.stack,
			} );
		} );

		window.addEventListener( 'unhandledrejection', ( event ) => {
			debug( 'Caught unhandled rejection:', event );

			this.sendErrorToAPI( {
				message: 'Unhandled Promise Rejection',
				trace: event.reason?.stack,
			} );
		} );
	}
}

let logger: RemoteLogger | null = null;

/**
 * Initializes the remote logger and automatic error handlers.
 * This function should be called once at the start of the application.
 *
 * @param config - Configuration object for the RemoteLogger.
 */
export function init( config: RemoteLoggerConfig ): void {
	if ( logger ) {
		debug( 'RemoteLogger is already initialized.' );
		return;
	}

	try {
		logger = new RemoteLogger( config );
		logger.initializeErrorHandlers();
	} catch ( error ) {
		// eslint-disable-next-line no-console
		console.error( 'Failed to initialize RemoteLogger:', error );
	}
}

/**
 * Logs a message or error, respecting rate limiting.
 *
 * This function is inefficient because the data goes over the REST API, so use sparingly.
 *
 * @param severity  - The severity of the log.
 * @param message   - The message to log.
 * @param extraData - Optional additional data to include in the log.
 */
export function log(
	severity: LogData[ 'severity' ],
	message: string,
	extraData?: Partial< LogData >
): void {
	if ( ! logger ) {
		debug( 'RemoteLogger is not initialized.' );
		return;
	}

	logger.log( severity, message, extraData );
}
