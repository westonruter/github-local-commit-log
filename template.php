<!DOCTYPE html>
<html>
    <head>
        <meta charset=utf-8>
        <title>Commit Log</title>
        <link rel=stylesheet href=style.css>
    </head>
    <body>
        <h1>Commit Log for <a target="_blank" href="<?php e($github_project_url . 'commits/' . $branch ) ?>"><code><?php e($branch) ?></code></a></h1>

        <ol reversed>
            <?php $commits = get_commits(); ?>
            <?php foreach($commits as $commit): ?>
                <?php
                $changeset = get_changeset($commit);
                if (empty($changeset)) {
                    continue;
                }
                ?>
                <li id="<?php e($commit->hash) ?>" style="<?php e('background: ' . get_hash_color($commit->hash)) ?>">
                    <h2>
                        <a target="_blank" href="<?php e($commit->github_url) ?>"><code><abbr title="<?php e($commit->hash) ?>"><?php e(substr($commit->hash, 0, 6)) ?></abbr></code></a>:
                        <?php e($commit->subject) ?>
                    </h2>
                    <p>
                        by <a href="<?php e('mailto:' . $commit->author_email) ?>"><?php e($commit->author_name) ?><img class='gravatar' src="<?php e(get_author_gravatar($commit)) ?>" alt="<?php e($commit->author_name) ?>"></a>,
                        <time datetime="<?php e(gmdate(DATE_W3C, strtotime($commit->date))) ?>" title="<?php e(date('r', strtotime($commit->date))) ?>"><?php e($commit->date_relative) ?></time>
                    </p>
                    <ul class="changeset">
                        <?php foreach($changeset as $change): ?>
                            <li class="<?php e(strtolower($change['status'])) ?>">
                                <a href="<?php e($commit->github_url . '#diff-' . $change['diff_number']) ?>" target="_blank" class="diff github-link">diff</a>
                                <a href="<?php e($commit->github_url . '?w=1#diff-' . $change['diff_number']) ?>" target="_blank" class="diff-w github-link">diff -w</a>
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
