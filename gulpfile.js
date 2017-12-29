/*
|--------------------------------------------------------------------------
| gulp modules
|--------------------------------------------------------------------------
| Load modules required in the tasks
*/
const gulp = require("gulp");
const mjml = require("gulp-mjml");
const extReplace = require("gulp-ext-replace");
const livereload = require("gulp-livereload");
const apidoc = require("gulp-apidoc");

// mjml-related constants
const mjmlSelector = [
    "resources/views/emails/*/*.blade.mjml",
    "!resources/views/emails/common/*.blade.mjml"
];
const mjmlOptions = {base: "resources/views/emails/"};

/*
|--------------------------------------------------------------------------
| mjml: Generate all mjml files
|--------------------------------------------------------------------------
*/
gulp.task("mjml", function () {
    gulp.src(mjmlSelector, mjmlOptions)
        .pipe(mjml())
        .pipe(gulp.dest("resources/views/emails/"))
        .pipe(extReplace(".php"))
        .pipe(gulp.dest("resources/views/emails/"))
});


/*
|--------------------------------------------------------------------------
| apidoc: Generate the documentation
|--------------------------------------------------------------------------
*/
gulp.task("apidoc", function (done) {
    apidoc({
        src: "./",
        dest: "docs/apidoc/",
        config: "./",
        template: "apidoc/templates/talview-material",
        includeFilters: ["apidoc/.*\\.php$", "app/Controllers/.*\\.php$"],
        silent: true,
        //debug: true
    }, done);
});

gulp.task("apidoc-watch", ["apidoc"], function() {
    livereload.listen();

    gulp.watch("docs/apidoc/index.html", function(event) {
        livereload.reload(event.path);
    });

    gulp.watch("docs/apidoc/**.js", function(event) {
        livereload.reload(event.path);
    });

    return gulp.watch("less/*.less", ["less"]);


});

/*
|--------------------------------------------------------------------------
| install: setup a build
|--------------------------------------------------------------------------
*/
gulp.task("install", ["mjml", "apidoc"]);


/*
|--------------------------------------------------------------------------
| mjml-watch: Generate mjml on the fly and use livereload
|--------------------------------------------------------------------------
*/
gulp.task("mjml-watch", ["mjml"], function () {
    livereload.listen();

    return gulp.watch(mjmlSelector, mjmlOptions, function (event) {
        gulp.src(event.path)
            .pipe(mjml())
            .pipe(gulp.dest("resources/views/emails/"))
            .pipe(extReplace(".php"))
            .pipe(gulp.dest("resources/views/emails/"))
            .pipe(livereload());
    });
});
