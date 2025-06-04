<?php
// BEGIN A2 CRON DISABLE
define('DISABLE_WP_CRON', true);
// END A2 CRON DISABLE
define( 'WP_CACHE', true );

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
define( 'DB_NAME', 'riyaoeiu_a2wp288' );

/** Database username */
define( 'DB_USER', 'riyaoeiu_a2wp288' );

/** Database password */
define( 'DB_PASSWORD', '7!S6p6kl]2' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

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
define( 'AUTH_KEY',         'ukasct1b6k45kkmfi0pf48wjdmjm1xfc6c9pdc3frgsbmvaomxnn3x18pc8xuelo' );
define( 'SECURE_AUTH_KEY',  '2h4tj0wan65i7xikdgbnsvok3xi7fueqe2orc8sfckhctrlgnv79cwf8o6tlxudf' );
define( 'LOGGED_IN_KEY',    '6yclpmxpcpfibponq8acoqweiqalfs9wszrhfyye7xlfg69steyl5kvr6w5rsjeb' );
define( 'NONCE_KEY',        'ibm37yazq7xtpj9e4wguddzjhk9xojyz7qhigyxijnrsrqqp2rlib4hvsur02tmf' );
define( 'AUTH_SALT',        'ugpaue5zrla7xvqaaco1xz7wzpcvfp7ygxfqdwtdszioocrczv3dd16sxi76f6j2' );
define( 'SECURE_AUTH_SALT', 't9e6j1ph9m2x617gmhn8arrry2vnlbdtcibrg1fa4szkydzjhfcgmlt1jevewxfq' );
define( 'LOGGED_IN_SALT',   '4rrgfgiup1h4qplsztaw8me96tvh4gdp2lqdcbtbmtl1590kds2c0rkq5jly8fo3' );
define( 'NONCE_SALT',       'xrqd8bo9zkt0cztlxej1md2sge5hj43cjqjslbwnmalbisyasm0tv4utprhana1s' );

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
$table_prefix = 'wp9i_';

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



define( 'WP_MEMORY_LIMIT', '128M' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
