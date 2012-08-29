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


function get_commits() {
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
    $commits = array_map(
        function ($commit) use ($separator, $pretty_format) {
            global $github_project_url;
            $commit = (object)array_combine(
                array_values($pretty_format),
                explode($separator, $commit, count($pretty_format))
            );
            $commit->github_url = $github_project_url . 'commit/' . $commit->hash;
            return $commit;
        },
        $commits
    );
    return $commits;
}

function get_changeset($commit) {
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
    return $changeset;
}

function changeset_filter($item) {
    if (isset($_GET['file'])) {
        return strpos($item['path'], $_GET['file']) !== false;
    }
    if (isset($_GET['type'])) {
        return preg_match('/' . preg_quote($_GET['type']) . '$/', $item['path']);
    }
    return true;
}

function get_author_gravatar($commit) {
    $author_gravatar = sprintf('http://www.gravatar.com/avatar/%s?s=64', md5(strtolower($commit->author_email)));
    return $author_gravatar;
}

function e($str) {
    echo htmlspecialchars($str, ENT_QUOTES);
}

function get_hash_color($hash, $opacity = 0.05) {
    assert(preg_match('/(\w\w)(\w\w)(\w\w)/', $hash, $matches));
    array_shift($matches);
    return sprintf('rgba(%d, %d, %d, %f)', $matches[0], $matches[1], $matches[2], $opacity);
}

require('template.php');
