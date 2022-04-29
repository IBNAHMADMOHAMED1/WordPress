<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         'mR3J$M~:x[V{~C?r{dvu<yqws;@_kvi+{<<Jq]^($TJ4Ufn.rl8?S9S;(x|NMY>e' );
define( 'SECURE_AUTH_KEY',  'dFes@ {qU{*H6kUnF6)#)otUDuj~ikqu),txUuj,&/tA%i+mC pP@kc1V,E8xJ)M' );
define( 'LOGGED_IN_KEY',    '!Y7lsGPl:lb-f3~K*$z#V4/3IkJnG*Q~Nh5B-Hb=4{3XmiVwq!/WAJ*iwT/;MGP^' );
define( 'NONCE_KEY',        ':%:QY]RPrP$= ~}gfZ9s/aIgMsTI27~d_ar_]u2e#+ur9F>,pWz^$iKE(NaLF*L,' );
define( 'AUTH_SALT',        '-q2?bnUr?c:y_t!2Y74jzPRU60c8jKnQ#dL+V.H7GTBc<^Zw~4/Z b2.-7e &34>' );
define( 'SECURE_AUTH_SALT', 'F{vyACkRx8}>~-HwUY- `QdCKj[yh@oIRV[1PKc0c3fCqCT.&:3TD1GKBy<N{a;N' );
define( 'LOGGED_IN_SALT',   '{5vjJHxOgEYg%Ps_m1k~r{oz[>r5P2l`{d1A(m_C[`EGbtvczD%tfpgYG@ KT&F{' );
define( 'NONCE_SALT',       '6|]KVf8PC.yX^`:*<`;i_6cY&t(` AP7E~EfK Z`6T~grX<V*rS?`Wd.;e34d_RA' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
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
