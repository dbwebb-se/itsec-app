<?php
/**
 * Configuration file for routes.
 */
return [
    // Load these routefiles in order specified and optionally mount them
    // onto a base route.
    "routeFiles" => [
        [
            // Base routes
            "mount" => null,
            "file" => __DIR__ . "/route/base.php",
        ],
        [
            // These are for internal error handling and exceptions
            "mount" => null,
            "file" => __DIR__ . "/route/internal.php",
        ],
        [
            // For debugging and development details on Anax
            "mount" => "debug",
            "file" => __DIR__ . "/route/debug.php",
        ],
        [
            // Keep this last since its a catch all
            "mount" => null,
            "file" => __DIR__ . "/route/404.php",
        ],
    ],

];
