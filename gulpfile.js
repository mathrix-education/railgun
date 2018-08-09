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
const mjml = require("gulp-mjml");
const imagemin = require("gulp-imagemin");

// mjml-related constants
const mjmlSelectors = {
    all: "resources/views/emails/**/*.mjml",
    common: "resources/views/emails/common/*.mjml",
    views: "resources/views/emails/*/*.blade.mjml",
    blade: "resources/views/emails/*/*.blade.php"
};

let mjmlPipeline = function (src) {
    return src.pipe(mjml())
        .pipe(extReplace(".php"))
        .pipe(replace("&#36;", "$")) // Unescape dollar signs, as MJML tends to escape blade syntax
        .pipe(gulp.dest("resources/views/emails/"))
        .pipe(livereload());
};

/**
 * Build all mjml views and send a livereload event
 */
gulp.task("mjml", function () {
    return mjmlPipeline(gulp.src(mjmlSelectors.views));
});

/**
 * Initialize livereload and watch for changes in mjml files
 */
gulp.task("mjml-watch", ["mjml"], function () {
    livereload.listen();

    // Common element changed: rebuild all views
    gulp.watch(mjmlSelectors.common, ["mjml"]);

    // A single view changed: rebuild and reload only the view
    gulp.watch(mjmlSelectors.views, function (event) {
        return mjmlPipeline(gulp.src(event.path));
    });
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
gulp.task("apidoc", function (done) {
    return apidoc({
        src: "./",
        dest: "docs/apidoc/",
        config: "./",
        //template: "apidoc/templates/apidoc-bs3-template",
        includeFilters: ["apidoc/.*\\.php$", "app/Controllers/.*\\.php$"],
        silent: true,
        //debug: true
    }, done);
});

/**
 * Compress cdn images
 */
gulp.task("cdn", function() {
    return gulp.src("storage/cdn/**")
        .pipe(imagemin([], {
            verbose: true
        }))
        .pipe(gulp.dest("storage/cdn/"));
});


gulp.task("install", ["mjml", "apidoc"]);
gulp.task("default", ["install"]);
gulp.task("clean", ["mjml-clean", "apidoc-clean"]);
