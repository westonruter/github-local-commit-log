GitHub provides a great interface for browsing commits and doing code reviews
via inline comments. However, when doing code reviews on projects that have lots
of commits both from template (frontend) developers and functional (backend)
developers, there is lots of noise that gets generated requiring you to wade
through commits that touch HTML and CSS files, when the changes I care about are
to PHP and JS files. Unfortuantely GitHub doesn't allow you to filter commits
down to those that only contain changes to a particular filetype. So this is
primarily what this functionality here provides.

By appending a `type=ext` query parameter, you will just see commits that
touched files of that type, and the list of files shown in that commit will just
be files of that type. If a commit does not contain a matching file, then the
commit will be skipped in the log.

In the list of the changed files in a commit, there are direct links to view
that specific file in the GitHub commit so you can review the changes and
provide inline comments. The browser's history then keeps track of your visit
and so can remember which changes have been reviewed based on the link
`:visited` style.

This little web app is intended to run on your local environment. Checkout the
repo somewhere on your system, then add a symlink into the docroot of your
project to point to the repo. Obviously be sure you `.gitignore` the symlink.
