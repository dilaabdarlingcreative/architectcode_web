<?php

use GoDaddy\WordPress\MWC\Core\Events\Site\SiteDescriptionEvent;
use GoDaddy\WordPress\MWC\Core\Events\Site\SiteLogoEvent;
use GoDaddy\WordPress\MWC\Core\Events\Site\SiteTitleEvent;

return [
    /*
     * --------------------------------------------------------------------------
     * Permalinks
     * --------------------------------------------------------------------------
     *
     * Allow plain permalinks. Since some MWCS features break when plain permalinks are set, we force "post name"
     * permalinks via `EnforcePostNamePermalinksInterceptor` by default.  This constant enables overriding that
     * feature in wp-config.php.
     *
     */
    'permalinks' => [
        'allowPlain' => defined('MWC_PERMALINKS_ALLOW_PLAIN') ? MWC_PERMALINKS_ALLOW_PLAIN : false,
    ],

    /*
     * --------------------------------------------------------------------------
     * Plugins
     * --------------------------------------------------------------------------
     */
    'plugins' => [
        /*
         * An array of blocked plugins directory slugs.
         */
        'blocked' => [
            '6scan-backup'                               => ['versionOrOlder' => ''],
            'adminer'                                    => ['versionOrOlder' => ''],
            'adsense-click-fraud-monitoring'             => ['versionOrOlder' => ''],
            'akeebabackupwp'                             => ['versionOrOlder' => ''],
            'automatic-wordpress-backup'                 => ['versionOrOlder' => ''],
            'backjacker'                                 => ['versionOrOlder' => ''],
            'backup'                                     => ['versionOrOlder' => ''],
            'backup-db'                                  => ['versionOrOlder' => ''],
            'backup-to-dropbox'                          => ['versionOrOlder' => ''],
            'backupbuddy'                                => ['versionOrOlder' => ''],
            'backupbuddy2.2.33'                          => ['versionOrOlder' => ''],
            'backupcreator'                              => ['versionOrOlder' => ''],
            'backupwordpress'                            => ['versionOrOlder' => ''],
            'backupwp'                                   => ['versionOrOlder' => ''],
            'backwpup'                                   => ['versionOrOlder' => ''],
            'broken-link-checker'                        => ['versionOrOlder' => ''],
            'broken-link-finder'                         => ['versionOrOlder' => ''],
            'cache-images'                               => ['versionOrOlder' => ''],
            'clef'                                       => ['versionOrOlder' => ''],
            'contextual-related-posts'                   => ['versionOrOlder' => ''],
            'counterize'                                 => ['versionOrOlder' => ''],
            'db-cache-reloaded'                          => ['versionOrOlder' => ''],
            'dbc-backup'                                 => ['versionOrOlder' => ''],
            'delete-all-comments'                        => ['versionOrOlder' => ''],
            'disable plugin updates'                     => ['versionOrOlder' => ''],
            'display-widgets'                            => ['versionOrOlder' => ''],
            'exploit-scanner'                            => ['versionOrOlder' => ''],
            'ezpz-one-click-backup'                      => ['versionOrOlder' => ''],
            'facebook'                                   => ['versionOrOlder' => ''],
            'firestats'                                  => ['versionOrOlder' => ''],
            'fuzzy-seo-booster'                          => ['versionOrOlder' => ''],
            'google-sitemap-generator'                   => ['versionOrOlder' => ''],
            'google-xml-sitemaps-with-multisite-support' => ['versionOrOlder' => ''],
            'gosquared-livestats'                        => ['versionOrOlder' => ''],
            'hcs-client'                                 => ['versionOrOlder' => ''],
            'hello.php'                                  => ['versionOrOlder' => ''],
            'hyper-cache'                                => ['versionOrOlder' => ''],
            'iwp-client'                                 => ['versionOrOlder' => ''],
            'jr-referrer'                                => ['versionOrOlder' => ''],
            'mailpoet'                                   => ['versionOrOlder' => '2.6.6'],
            'newstatpress'                               => ['versionOrOlder' => ''],
            'nextgen-gallery'                            => ['versionOrOlder' => ''],
            'p3-profiler'                                => ['versionOrOlder' => ''],
            'pipdig'                                     => ['versionOrOlder' => ''],
            'portable-phpmyadmin'                        => ['versionOrOlder' => ''],
            'pressbackup'                                => ['versionOrOlder' => ''],
            'real-time-find-and-replace'                 => ['versionOrOlder' => ''],
            'referrer-wp'                                => ['versionOrOlder' => ''],
            'repress'                                    => ['versionOrOlder' => ''],
            'search-unleashed'                           => ['versionOrOlder' => ''],
            'sendpress-email-marketing'                  => ['versionOrOlder' => ''],
            'seo-alrp'                                   => ['versionOrOlder' => ''],
            'sgcachepress'                               => ['versionOrOlder' => ''],
            'similar-posts'                              => ['versionOrOlder' => ''],
            'simple-backup'                              => ['versionOrOlder' => ''],
            'simple-stats'                               => ['versionOrOlder' => ''],
            'simple-wordpress-backup'                    => ['versionOrOlder' => ''],
            'slick-popup'                                => ['versionOrOlder' => ''],
            'smestorage-multi-cloud-files-p'             => ['versionOrOlder' => ''],
            'snapshot'                                   => ['versionOrOlder' => ''],
            'snapshot-backup'                            => ['versionOrOlder' => ''],
            'statpress'                                  => ['versionOrOlder' => ''],
            'statpress-reloaded'                         => ['versionOrOlder' => ''],
            'statpress-visitors'                         => ['versionOrOlder' => ''],
            'stats'                                      => ['versionOrOlder' => ''],
            'synthesis'                                  => ['versionOrOlder' => ''],
            'the-codetree-backup'                        => ['versionOrOlder' => ''],
            'timthumb-vulnerability-scanner'             => ['versionOrOlder' => ''],
            'toolspack'                                  => ['versionOrOlder' => ''],
            'total-archive-by-fotan'                     => ['versionOrOlder' => ''],
            'total-backup'                               => ['versionOrOlder' => ''],
            'track-that-stat'                            => ['versionOrOlder' => ''],
            'updraft'                                    => ['versionOrOlder' => ''],
            'updraftplus'                                => ['versionOrOlder' => ''],
            'viberspy-pro'                               => ['versionOrOlder' => ''],
            'visitor-stats-widget'                       => ['versionOrOlder' => ''],
            'vm-backups'                                 => ['versionOrOlder' => ''],
            'vsf-simple-stats'                           => ['versionOrOlder' => ''],
            'w3-total-cache'                             => ['versionOrOlder' => ''],
            'wassup'                                     => ['versionOrOlder' => ''],
            'wordpress-backup'                           => ['versionOrOlder' => ''],
            'wordpress-backup-to-dropbox'                => ['versionOrOlder' => ''],
            'wordpress-beta-tester'                      => ['versionOrOlder' => ''],
            'wordpress-database-backup'                  => ['versionOrOlder' => ''],
            'wordpress-popular-posts'                    => ['versionOrOlder' => ''],
            'wp-cache'                                   => ['versionOrOlder' => ''],
            'wp-cachecom'                                => ['versionOrOlder' => ''],
            'wp-complete-backup'                         => ['versionOrOlder' => ''],
            'wp-copysafe-pdf'                            => ['versionOrOlder' => ''],
            'wp-copysafe-web'                            => ['versionOrOlder' => ''],
            'wp-database-backup'                         => ['versionOrOlder' => ''],
            'wp-database-optimizer'                      => ['versionOrOlder' => ''],
            'wp-db-backup'                               => ['versionOrOlder' => ''],
            'wp-dbmanager'                               => ['versionOrOlder' => ''],
            'wp-engine-snapshot'                         => ['versionOrOlder' => ''],
            'wp-fast-cache'                              => ['versionOrOlder' => ''],
            'wp-fastest-cache'                           => ['versionOrOlder' => ''],
            'wp-file-cache'                              => ['versionOrOlder' => ''],
            'wp-live-chat-support'                       => ['versionOrOlder' => ''],
            'wp-mailinglist'                             => ['versionOrOlder' => ''],
            'wp-maintenance-mode'                        => ['versionOrOlder' => ''],
            'wp-optimize'                                => ['versionOrOlder' => ''],
            'wp-phpmyadmin'                              => ['versionOrOlder' => ''],
            'wp-postviews'                               => ['versionOrOlder' => ''],
            'wp-power-stats'                             => ['versionOrOlder' => ''],
            'wp-s3-backups'                              => ['versionOrOlder' => ''],
            'wp-slimstat'                                => ['versionOrOlder' => ''],
            'wp-statistics'                              => ['versionOrOlder' => ''],
            'wp-super-cache'                             => ['versionOrOlder' => ''],
            'wp-time-machine'                            => ['versionOrOlder' => ''],
            'wpdbspringclean'                            => ['versionOrOlder' => ''],
            'wpengine-common'                            => ['versionOrOlder' => ''],
            'wponlinebackup'                             => ['versionOrOlder' => ''],
            'xcloner-backup-and-restore'                 => ['versionOrOlder' => ''],
            'xml-sitemap-feed'                           => ['versionOrOlder' => ''],
            'yet-another-featured-posts-plugin'          => ['versionOrOlder' => ''],
            'youtube-sidebar-widget'                     => ['versionOrOlder' => ''],
        ],
        /*
         * An array with information about plugins that must not be manually updated, deactivated or deleted.
         */
        'locked' => [
            // example to illustrate formatting:
            /*
            [
                'name'     => 'WooCommerce',
                'basename' => 'woocommerce/woocommerce.php',
            ],
            */
        ],
    ],
    /*
     * --------------------------------------------------------------------------
     * Monitored Items
     * --------------------------------------------------------------------------
     *
     * WordPress has some common data structures. Any data structures listed below will be monitored using Event Bridge.
     *
     */
    'monitoredItems' => [
        'options' => [
            'site_logo'       => [SiteLogoEvent::class],
            'blogname'        => [SiteTitleEvent::class],
            'blogdescription' => [SiteDescriptionEvent::class],
        ],
    ],
];
