# gulp tasks

In our `gulpfile.js`, there are a few tasks defined to make our life easier. The page describes the role of all of them.

## MJML tasks
### mjml
Build all mjml views and send a livereload event

### mjml-watch
Initialize livereload and watch for changes in mjml files

### mjml-clean
Clean all generated mjml files (*.blade.php)

## apidoc tasks
### apidoc
Build documentation using apidoc

### apidoc-clean
Clean the documentation directory

## General tasks
### install
Run `apidoc` and `mjml` tasks

### clean
Run `apidoc-clean` and `mjml-clean` tasks

Note: as there is no `apidoc-watch` task, we did not have defined and global `watch` task.
