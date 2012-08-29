<?php
/**
 * GitHub Local Commit Log
 * @author Weston Ruter
 * https://github.com/westonruter/github-local-commit-log
 */

chdir($_SERVER['DOCUMENT_ROOT']); // Necessary when commit-log is symlinked from another project

$remotes_raw = `git remote -v`;
if (!preg_match('#^origin\s+.*?github.com(?:/|:)([^/]+)/(.+?)\.git#m', $remotes_raw, $matches)) {
    throw new Exception('Unable to find origin remote on GitHub');
}

$github_account = $matches[1];
$github_repo = $matches[2];
$github_project_url = "https://github.com/{$github_account}/{$github_repo}/";

$branch = str_replace('refs/heads/', '', `git symbolic-ref HEAD`);
$github_branch_base_url = $github_project_url . 'tree/' . $branch . '/';
$github_branch_blame_base_url = $github_project_url . 'blame/' . $branch . '/';

$pretty_format = array(
    '%H' => 'hash',
    '%an' => 'author_name',
    '%ae' => 'author_email',
    '%ad' => 'date',
    '%ar' => 'date_relative',
    '%s' => 'subject',
);
$separator = "\t\t";

exec('git log --pretty=' . escapeshellarg(join($separator, array_keys($pretty_format))), $commits, $retval);


function changeset_filter($item) {
    if (isset($_GET['file'])) {
        return strpos($item['path'], $_GET['file']) !== false;
    }
    if (isset($_GET['type'])) {
        return preg_match('/' . preg_quote($_GET['type']) . '$/', $item['path']);
    }
    return true;
}

function e($str) {
    echo htmlspecialchars($str, ENT_QUOTES);
}

function get_hash_color($hash, $opacity = 0.05) {
    assert(preg_match('/(\w\w)(\w\w)(\w\w)/', $hash, $matches));
    array_shift($matches);
    return sprintf('rgba(%d, %d, %d, %f)', $matches[0], $matches[1], $matches[2], $opacity);
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset=utf-8>
        <title>Commit Log</title>
        <link rel=stylesheet href=style.css>
    </head>
    <body>
        <h1>Commit Log <code><?php e($branch) ?></code></h1>

        <ol reversed>
            <?php $i = count($commits); ?>
            <?php foreach($commits as $commit): ?>
                <?php
                $commit = (object)array_combine(
                    array_values($pretty_format),
                    explode($separator, $commit, count($pretty_format))
                );
                $changeset = array();
                exec('git show --pretty="format:" --name-status ' . escapeshellarg($commit->hash), $changeset, $retval);
                $changeset = array_values(array_filter(array_map('trim', array_filter($changeset))));
                $changeset = array_map(
                    function($item){
                        static $diff_number = -1;
                        $diff_number += 1;
                        list($status, $path) = explode("\t", $item);
                        return compact('status', 'path', 'diff_number');
                    },
                    $changeset
                );
                $changeset = array_filter($changeset, 'changeset_filter');
                if (empty($changeset)) {
                    continue;
                }
                $commit_url = $github_project_url . 'commit/' . $commit->hash;
                $author_gravatar = sprintf('http://www.gravatar.com/avatar/%s?s=64', md5(strtolower($commit->author_email)));
                ?>
                <li id="<?php e($commit->hash) ?>" style="<?php e('background: ' . get_hash_color($commit->hash)) ?>">
                    <h2>
                        <a target="_blank" href="<?php e($commit_url) ?>"><code><abbr title="<?php e($commit->hash) ?>"><?php e(substr($commit->hash, 0, 6)) ?></abbr></code></a>:
                        <?php e($commit->subject) ?>
                    </h2>
                    <p>
                        by <a href="<?php e('mailto:' . $commit->author_email) ?>"><?php e($commit->author_name) ?><img class='gravatar' src="<?php e($author_gravatar) ?>" alt="<?php e($commit->author_name) ?>"></a>,
                        <time datetime="<?php e(gmdate(DATE_W3C, strtotime($commit->date))) ?>" title="<?php e(date('r', strtotime($commit->date))) ?>"><?php e($commit->date_relative) ?></time>
                    </p>
                    <ul class="changeset">
                        <?php foreach($changeset as $change): ?>
                            <li class="<?php e(strtolower($change['status'])) ?>">
                                <a href="<?php e($commit_url . '#diff-' . $change['diff_number']) ?>" target="_blank" class="diff github-link">diff</a>
                                <a href="<?php e($commit_url . '?w=1#diff-' . $change['diff_number']) ?>" target="_blank" class="diff-w github-link">diff -w</a>
                                <a href="<?php e($github_branch_base_url . $change['path']) ?>" target="_blank" class="head github-link">head</a>
                                <a href="<?php e($github_branch_blame_base_url . $change['path']) ?>" target="_blank" class="blame github-link">blame</a>

                                <span class="status"><?php e($change['status']) ?></span>
                                <span class="path"><?php e($change['path']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <?php flush(); ?>
            <?php endforeach; ?>
        </ol>
    </body>
</html>
