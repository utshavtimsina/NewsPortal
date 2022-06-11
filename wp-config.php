<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'khabared_ucation' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'educ@tion123' );

/** MySQL hostname */
define( 'DB_HOST', '3.111.21.155' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'gT`+(HTfCT$)dBGki7LGM/1C`>=}NL|cs#C4TL?&%X]fJ+w^5W<@+f5pA9$EUGl)' );
define( 'SECURE_AUTH_KEY',   '6y&j|IIb&_?)+^RB{hYO*SgxU#b%HPo=~e#m!IY+;H0<yk6yIFI{*INP,oF*5;sM' );
define( 'LOGGED_IN_KEY',     ';KZY)VaC*d?Mv~i;[(I=j%<KUGI7uB<GKeILI[Q53&3Y]}Unoy79J+aZe..N7!M[' );
define( 'NONCE_KEY',         'IWxwt-d0(*kgWmc*1DQ$ic zUgV.I-xm4J^h!G`e?QdZ%k)&p@*Nh:Vk2P1G[0#0' );
define( 'AUTH_SALT',         '~yRi3qO!JDTu}kN+jjSXC.9d*x3%o9$kDWRs2$HH $Sp?I!bF/3Tlt-3v7e~73P|' );
define( 'SECURE_AUTH_SALT',  'k_cPi63|x6uoD^$Nu 2s6{V2npDI#J6>p^G:hQ?$uBv&S3CIzp;@+DW`90vi:{Hm' );
define( 'LOGGED_IN_SALT',    'Wzu^W,: 6fFi*T%/FD:?PSxIOo3`g6ghYy(Igw%.SU%TH}./aGTmO_S^8^O*UC,&' );
define( 'NONCE_SALT',        'Q4<Iu/!6_0~<8_i35l3E*doj9<p#p.u^|iVMK!CtaEW#[Uf}^+`bp=lu8-R_{B17' );
define( 'WP_CACHE_KEY_SALT', 'sIhDz*#E9;Ba<~!^?W8iq*w]T0|OVNyah5&&s>ram1heV?xgGpllh>^P#lo6J_M|' );

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';




/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) )
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
