/*
|--------------------------------------------------------------------------
| gulp modules
|--------------------------------------------------------------------------
| Load modules required in the tasks
*/
const gulp = require("gulp");
const extReplace = require("gulp-ext-replace");
const livereload = require("gulp-livereload");
const apidoc = require("gulp-apidoc");
const clean = require("gulp-clean");
const replace = require("gulp-replace");
mjml = require("gulp-mjml");

// mjml-related constants
const mjmlSelectors = {
    all: "resources/views/emails/**/*.mjml",
    common: "resources/views/emails/common/*.mjml",
    views: "resources/views/emails/*/*.blade.mjml",
    blade: "resources/views/emails/*/*.blade.php"
};

/**
 * Build all mjml views and send a livereload event
 */
gulp.task("mjml", function () {
    return gulp.src(mjmlSelectors.views)
        .pipe(mjml())
        .pipe(extReplace(".php"))
        .pipe(replace("&#36;", "$")) // Unescape dollar signs
        .pipe(gulp.dest("resources/views/emails/"))
        .pipe(livereload());
});

/**
 * Initialize livereload and watch for changes in mjml files
 */
gulp.task("mjml-watch", ["mjml"], function () {
    livereload.listen();
    gulp.watch(mjmlSelectors.all, ["mjml"]);
});

/**
 * Clean all generated mjml files (*.blade.php)
 */
gulp.task("mjml-clean", function () {
    return gulp.src(mjmlSelectors.blade, {read: false}).pipe(clean());
});


/**
 * Build documentation using apidoc
 */
gulp.task("apidoc", ["apidoc-clean"], function (done) {
    return apidoc({
        src: "./",
        dest: "docs/apidoc/",
        config: "./",
        template: "apidoc/templates/apidoc-bs3-template",
        includeFilters: ["apidoc/.*\\.php$", "app/Controllers/.*\\.php$"],
        silent: true,
        //debug: true
    }, done);
});

/**
 * Clean the documentation directory
 */
gulp.task("apidoc-clean", function () {
    return gulp.src("docs/apidoc/", {read: false}).pipe(clean());
});


gulp.task("install", ["mjml", "apidoc"]);
gulp.task("default", ["install"]);
gulp.task("clean", ["mjml-clean", "apidoc-clean"]);
