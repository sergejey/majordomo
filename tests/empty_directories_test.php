<?php
require dirname(__DIR__) . '/lib/syncfiles.class.php';
set_error_handler(function ($n, $s) { if (error_reporting() & $n) throw new Exception($s); });
function verify($ok, $label) { if (!$ok) throw new Exception('FAIL: '.$label); echo 'PASS: '.$label.PHP_EOL; }
$r = sys_get_temp_dir().'/md-cleanup-'.bin2hex(random_bytes(8));
mkdir($r);
try {
    mkdir($r.'/empty/nested', 0700, true);
    verify(removeEmptySubFolders($r.'/empty') === true && !is_dir($r.'/empty'), 'nested empty directories');
    mkdir($r.'/mixed/empty', 0700, true);
    file_put_contents($r.'/mixed/aaa', 'keep');
    verify(removeEmptySubFolders($r.'/mixed') === false && file_get_contents($r.'/mixed/aaa') === 'keep' && !is_dir($r.'/mixed/empty'), 'nonempty parent and empty sibling');
    mkdir($r.'/hidden/.empty', 0700, true);
    file_put_contents($r.'/hidden/.keep', 'keep');
    verify(removeEmptySubFolders($r.'/hidden') === false && file_exists($r.'/hidden/.keep') && !is_dir($r.'/hidden/.empty'), 'hidden entries');
    mkdir($r.'/target'); mkdir($r.'/links');
    symlink($r.'/target', $r.'/links/dir');
    symlink($r.'/absent', $r.'/links/dangling');
    symlink($r.'/mixed/aaa', $r.'/links/file');
    verify(removeEmptySubFolders($r.'/links') === false && is_dir($r.'/target') && is_link($r.'/links/dangling') && is_link($r.'/links/file'), 'symlinks preserved');
    verify(removeEmptySubFolders($r.'/links/dir/') === false && is_dir($r.'/target'), 'root symlink preserved');
    verify(removeEmptySubFolders($r.'/missing') === false && removeEmptySubFolders($r.'/mixed/aaa') === false, 'missing and file paths');
    echo "ALL TESTS PASSED\n";
} finally {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($r, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($it as $f) {
        if ($f->isLink() || !$f->isDir()) unlink($f->getPathname()); else rmdir($f->getPathname());
    }
    rmdir($r);
}
