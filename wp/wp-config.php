<?php

/**

 * The base configurations of the WordPress.

 *

 * This file has the following configurations: MySQL settings, Table Prefix,

 * Secret Keys, WordPress Language, and ABSPATH. You can find more information

 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing

 * wp-config.php} Codex page. You can get the MySQL settings from your web host.

 *

 * This file is used by the wp-config.php creation script during the

 * installation. You don't have to use the web site, you can just copy this file

 * to "wp-config.php" and fill in the values.

 *

 * @package WordPress

 */



// ** MySQL settings - You can get this info from your web host ** //

/** Database for info WordPress */

switch($_SERVER['HTTP_HOST']){
	
	case "icuinparis.dev" :
		define('DB_NAME', 'icuinparis_wp');
		define('DB_USER', 'root');
		define('DB_PASSWORD', 'root');
		break;

	case "dev.icuinparis.com" :

		define('DB_NAME', 'cinsummer_dev_wp');
		define('DB_USER', 'cinsummer');
		define('DB_PASSWORD', 'christans1234');
		break;

	default : 
		define('DB_NAME', 'cinsummer_prod_wp');
		define('DB_USER', 'cinsummer');
		define('DB_PASSWORD', 'christans1234');

}

define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

define('DISABLE_CACHE', true);

/**#@+

 * Authentication Unique Keys and Salts.

 *

 * Change these to different unique phrases!

 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}

 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.

 *

 * @since 2.6.0

 */

define('AUTH_KEY',         '@j?ipQ1nR,yuuCW@Ustage435.comAS-{%gQEpSr_+<dyb+>:Yd#nWbetyh~4rmMUap4Q6rZEcZy');
define('SECURE_AUTH_KEY',  '+hOIBXnJ~C;ftmT([CA|]_wDSstage435.comG<K?#S8{H>//EaLy7]h:jhRfj.K=Usg#g&$9+ox');
define('LOGGED_IN_KEY',    'c8K?-u_wU{BZ2yHK_sbOo1@?!stage435.com}H<`PM%7^l6VJTY,~DSOJ,zCtVI@Ym$WZi1@5x5');
define('NONCE_KEY',        's5,9YV+%:+HFX#l~ %RE`AZ/pstage435.comUzn<&<R%71t-|[H-L+}AtN9/thH&dMcVM8WN|Q}');
define('AUTH_SALT',        'KUEIq@~d.Tk+~t>1:HS9$8G_*stage435.comzG,jcuq2l=7l#KE[-1c)QW3a{LwGi-kwhRVP&]g');
define('SECURE_AUTH_SALT', ';0GoKVCGWIZh:YOa*h[]-T&Distage435.comnp=:iQ;z$>OkEYNi2@Y`|5-c|n:Jb #}97E?LX7');
define('LOGGED_IN_SALT',   'we3RP{hVolwbVh-((L%LEcHKlstage435.com[IaA9<bDvi`h/M:3U7xK8S]A|.Q,2$|*jcOqWNB');
define('NONCE_SALT',       '~<MckLITBiGaIV)497^JDbe-)stage435.comG:*}/Prup?HeQMLNLz2kG~d/306X7Noin@gGh7+');


/**#@-*/



/**

 * WordPress Database Table prefix.

 *

 * You can have multiple installations in one database if you give each a unique

 * prefix. Only numbers, letters, and underscores please!

 */

$table_prefix  = 'wp_';



/**

 * WordPress Localized Language, defaults to English.

 *

 * Change this to localize WordPress. A corresponding MO file for the chosen

 * language must be installed to wp-content/languages. For example, install

 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German

 * language support.

 */

define('WPLANG', '');



/**

 * For developers: WordPress debugging mode.

 *

 * Change this to true to enable the display of notices during development.

 * It is strongly recommended that plugin and theme developers use WP_DEBUG

 * in their development environments.

 */

define('WP_DEBUG', false);



/* That's all, stop editing! Happy blogging. */



/** Absolute path to the WordPress directory. */

if ( !defined('ABSPATH') )

	define('ABSPATH', dirname(__FILE__) . '/');



/** Sets up WordPress vars and included files. */

require_once(ABSPATH . 'wp-settings.php');

