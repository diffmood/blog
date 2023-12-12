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
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'test' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

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
define( 'AUTH_KEY',         ' o>2w)SxgKuX1c5R/m%NR[Pjbq{3SN}KS$*4Vs-NJFouk;wQc!(U@q5; oL@h- f' );
define( 'SECURE_AUTH_KEY',  'kQjF}eP&KID3`7*=^U<%#ddQJJ(<jOWpjyzq+_4y^%OC5+g2xwGud70i:YGkzk5J' );
define( 'LOGGED_IN_KEY',    '>Epfz]2y+;4l7faVP!yppi1uG<}Z(3]pg3To_)K4`DZ7r>?+1xrAv{^3{Eug;h)*' );
define( 'NONCE_KEY',        'OUm~,SMqsl%lQ.qcxZ<[)LsXc:nARj|C[2OBw ht~#p|7~]==. ||F$d4kKAtjCz' );
define( 'AUTH_SALT',        'rv0IrMfz$J/[1hxGeI_&)byneL;m6&e1s7}qT`AB-;RiI3F8Y%Hr9ZiXrOScSgnH' );
define( 'SECURE_AUTH_SALT', 'wA%bs&9+i|b+EN)3KZk;riTHcX=_K60!PuIb^Jb:N:C )9x.V)lJ(JFOn5tr4]+j' );
define( 'LOGGED_IN_SALT',   'Mt8BOFV}q:+m1F1-(<78R[Ak,goh!+$G6XX dIkEQ4*Hn$m`jY={)uThfJYp4*Io' );
define( 'NONCE_SALT',       'PgMS_k1,n~N(H?*vz@hR[z]tgQN*<kMHZkflhlS JFH8nceBnfPhZi{tDjFYDVdG' );

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
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
