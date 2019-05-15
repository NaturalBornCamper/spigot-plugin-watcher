<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

//echo phpinfo();
//exit;

if (!isset($_GET['email']))
    exit('No \'email\' $_GET parameter sent, cannot send updates');

require __DIR__ . '/vendor/autoload.php';

use \Rct567\DomQuery\DomQuery;

$CACHE_DIRECTORY = __DIR__ . '/cache';
$SPIGOT_SELECTORS = (object)[
    'NAME' => '.resourceHistory td.version',
    'DATE' => '.resourceHistory .DateTime',
];
$BUKKIT_SELECTORS = (object)[
    'NAME' => '.project-file-listing .twitch-link',
    'DATE' => '.project-file-listing .project-file-date-uploaded abbr',
];

$WATCHES = [
    (object)[
        'name' => 'Spigot KeepInventory',
        'url' => 'https://www.spigotmc.org/resources/keepinventory.1638/history',
        'nameSelector' => $SPIGOT_SELECTORS->NAME,
        'dateSelector' => $SPIGOT_SELECTORS->DATE
    ],
    (object)[
        'name' => 'Bukkit KeepInventory',
        'url' => 'https://dev.bukkit.org/projects/keep-inventory/files',
        'nameSelector' => $BUKKIT_SELECTORS->NAME,
        'dateSelector' => $BUKKIT_SELECTORS->DATE
    ],

    (object)[
        'name' => 'Spigot Multiverse Core',
        'url' => 'https://www.spigotmc.org/resources/multiverse-core.64450/history',
        'nameSelector' => $SPIGOT_SELECTORS->NAME,
        'dateSelector' => $SPIGOT_SELECTORS->DATE
    ],
    (object)[
        'name' => 'Bukkit Multiverse Core',
        'url' => 'https://dev.bukkit.org/projects/multiverse-core/files',
        'nameSelector' => $BUKKIT_SELECTORS->NAME,
        'dateSelector' => $BUKKIT_SELECTORS->DATE
    ],

    (object)[
        'name' => 'Bukkit Multiverse Portals',
        'url' => 'https://dev.bukkit.org/projects/multiverse-portals/files',
        'nameSelector' => $BUKKIT_SELECTORS->NAME,
        'dateSelector' => $BUKKIT_SELECTORS->DATE
    ],

    (object)[
        'name' => 'Spigot SimpleFly1.1',
        'url' => 'https://www.spigotmc.org/resources/simplefly.354/history',
        'nameSelector' => $SPIGOT_SELECTORS->NAME,
        'dateSelector' => $SPIGOT_SELECTORS->DATE
    ],

    (object)[
        'name' => 'Spigot SimpleFLY1.2',
        'url' => 'https://www.spigotmc.org/resources/simplefly.38568/history',
        'nameSelector' => $SPIGOT_SELECTORS->NAME,
        'dateSelector' => $SPIGOT_SELECTORS->DATE
    ],

    (object)[
        'name' => 'Spigot Dynmap',
        'url' => 'https://www.spigotmc.org/resources/dynmap.274/history',
        'nameSelector' => $SPIGOT_SELECTORS->NAME,
        'dateSelector' => $SPIGOT_SELECTORS->DATE
    ],
    (object)[
        'name' => 'Bukkit Dynmap',
        'url' => 'https://dev.bukkit.org/projects/dynmap/files',
        'nameSelector' => $BUKKIT_SELECTORS->NAME,
        'dateSelector' => $BUKKIT_SELECTORS->DATE
    ],

    (object)[
        'name' => 'Bukkit Dynmap-Mobs',
        'url' => 'https://dev.bukkit.org/projects/dynmap-mobs/files',
        'nameSelector' => $BUKKIT_SELECTORS->NAME,
        'dateSelector' => $BUKKIT_SELECTORS->DATE
    ],

    (object)[
        'name' => 'Spigot Dynmap-Structures',
        'url' => 'https://www.spigotmc.org/resources/dynmap-structures.39534/history',
        'nameSelector' => $SPIGOT_SELECTORS->NAME,
        'dateSelector' => $SPIGOT_SELECTORS->DATE
    ],
    (object)[
        'name' => 'Bukkit Dynmap-Structures',
        'url' => 'https://dev.bukkit.org/projects/dynmap-structures/files',
        'nameSelector' => $BUKKIT_SELECTORS->NAME,
        'dateSelector' => $BUKKIT_SELECTORS->DATE
    ],

    (object)[
        'name' => 'Bukkit WorldEdit',
        'url' => 'https://dev.bukkit.org/projects/worldedit/files',
        'nameSelector' => $BUKKIT_SELECTORS->NAME,
        'dateSelector' => $BUKKIT_SELECTORS->DATE
    ],

    (object)[
        'name' => 'Spigot AsyncWorldEdit',
        'url' => 'https://www.spigotmc.org/resources/asyncworldedit.327/history',
        'nameSelector' => $SPIGOT_SELECTORS->NAME,
        'dateSelector' => $SPIGOT_SELECTORS->DATE
    ],
];

$context = stream_context_create(
    array(
        "http" => array(
            "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
        )
    )
);

if (!is_dir($CACHE_DIRECTORY)) {
    mkdir($CACHE_DIRECTORY);
}

foreach ($WATCHES as $watch) {
    $dom = new DomQuery(file_get_contents($watch->url, false, $context));
    $latestVersion = $dom->find($watch->nameSelector)->text();

    $identifier = str_replace(' ', '_', strtolower($watch->name));
    $cachedFilename = "{$CACHE_DIRECTORY}/{$identifier}.ini";
    $cachedVersion = 'unknown';
    if (is_file($cachedFilename)) {
        $cachedVersion = file_get_contents($cachedFilename);
        if ($cachedVersion == $latestVersion) {
            continue;
        }
    }

    // Send notification email
    $updateDate = $dom->find($watch->dateSelector)->text();
    $subject = $watch->name . ' plugin updated';
    $body = "The \"{$watch->name}\" plugin was recently updated on {$updateDate} to version \"{$latestVersion}\"" . PHP_EOL;
    $body .= "The previous version was \"{$cachedVersion}\"" . PHP_EOL . PHP_EOL;
    $body .= 'Download or more info at ' . $watch->url;

    echo 'SENDING EMAIL:<br>';
    echo $subject . '<br>';
    echo nl2br($body) . '<br><hr><br>';

    $headers = 'From: spigot-plugin-watcher@example.com' . "\r\n" .
        'Reply-To: no-reply@example.com' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    mail($_GET['email'], $subject, $body, $headers);

    // Cache latest version sent by mail
    file_put_contents($cachedFilename, $latestVersion);
}
