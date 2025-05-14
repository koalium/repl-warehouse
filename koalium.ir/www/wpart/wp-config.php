<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wpart' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

if ( !defined('WP_CLI') ) {
    define( 'WP_SITEURL', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
    define( 'WP_HOME',    $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
}



/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'Vq2ljincx1UgOivwrzWyKJTcqUKH4MKvzorJBNvwbWv68Gbe8zgnp5HDpnjjSMMW' );
define( 'SECURE_AUTH_KEY',  'lkurWpjkzUK1TXzICAVuHSu1uRWp5BY4s2yyeBpKU9Lt993apoOTWQhV37anNFHq' );
define( 'LOGGED_IN_KEY',    'tLSUYHEz8T3n4ExiKXV6k0M63A7UZBb5CiQJiWzTC8lHpH13fzx9gnF2sVrjy40f' );
define( 'NONCE_KEY',        'Wk10o7g8f7FiFK6KQN1Kz3yBrOQLUPK5lUmAICRmm2EdfGCep6oBCSC3zvxXQoQZ' );
define( 'AUTH_SALT',        'CRcOcoWWIgDNVa2VD6pDHbRfPRh7xhjpwq7xuWjy855aBfu9cH9UryHa0Fmkh4Rv' );
define( 'SECURE_AUTH_SALT', 'nTqHcC0oknN6teFZMAtCfWWsJk5bxkSWXps3s5l7Sb4Pc8YpNRPE1kRlt036KT2U' );
define( 'LOGGED_IN_SALT',   'xH5uogjzE34840laMnJqI4FNNyZMq18TLGsezqTeFXPVdUReA5XzpOY0ZeL709et' );
define( 'NONCE_SALT',       '4Du4Y2K5yUDH7tAcJD5hNMRyHivLiVIO7jH837yGuEjiO9nnGCcIRhEQc6iP8kFW' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
