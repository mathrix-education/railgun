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


// mjml-related constants
const mjml = {
    engine: require("gulp-mjml"),
    selectors: {
        all: "resources/views/emails/**/*.mjml",
        common: "resources/views/emails/common/*.mjml",
        views: "resources/views/emails/*/*.blade.mjml",
        blade: "resources/views/emails/*/*.blade.php"
    }
};

gulp.task("mjml", function () {
    return gulp.src(mjml.selectors.views)
        .pipe(mjml.engine())
        .pipe(extReplace(".php"))
        .pipe(replace("&#36;", "$")) // Unescape dollar signs
        .pipe(gulp.dest("resources/views/emails/"))
        .pipe(livereload());
});

gulp.task("mjml-watch", ["mjml"], function () {
    livereload.listen();
    gulp.watch(mjml.selectors.all, ["mjml"]);
});

gulp.task("mjml-clean", function () {
    return gulp.src(mjml.selectors.blade, {read: false}).pipe(clean());
});

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

gulp.task("apidoc-clean", function () {
    return gulp.src("docs/apidoc/", {read: false}).pipe(clean());
});


gulp.task("install", ["mjml", "apidoc"]);

gulp.task("default", ["install"]);
