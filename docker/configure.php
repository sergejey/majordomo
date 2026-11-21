<?php
/**
 * Generate config.php from environment variables using config.php.sample as
 * a template. Only DB_* values are overridden; every other core setting keeps
 * its sample default. Passwords are emitted via var_export(), so quotes and
 * backslashes are escaped correctly.
 */

$root = dirname(__DIR__);
$sample = $root . '/config.php.sample';
$src = @file_get_contents($sample);
if ($src === false) {
    fwrite(STDERR, "Cannot read $sample\n");
    exit(1);
}

$env = function ($name, $default) {
    $value = getenv($name);
    return ($value === false || $value === '') ? $default : $value;
};

$values = array(
    'DB_HOST'     => $env('DB_HOST', 'db'),
    'DB_NAME'     => $env('DB_NAME', 'db_terminal'),
    'DB_USER'     => $env('DB_USER', 'mj'),
    'DB_PASSWORD' => $env('DB_PASSWORD', 'mj'),
);

foreach ($values as $key => $value) {
    $pattern = "/^Define\('" . preg_quote($key, '/') . "',\\s*[^;]*\\);/m";
    $src = preg_replace_callback($pattern, function ($m) use ($key, $value) {
        return "Define('" . $key . "', " . var_export((string)$value, true) . ");";
    }, $src, 1, $count);
    if ($count !== 1) {
        fwrite(STDERR, "Cannot patch $key in config.php.sample\n");
        exit(1);
    }
}

if (@file_put_contents($root . '/config.php', $src) === false) {
    fwrite(STDERR, "Cannot write $root/config.php\n");
    exit(1);
}
