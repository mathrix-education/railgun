/*
|--------------------------------------------------------------------------
| gulp modules
|--------------------------------------------------------------------------
*/
const gulp = require('gulp');
const mjml = require('gulp-mjml');
const extReplace = require('gulp-ext-replace');
const livereload = require('gulp-livereload');


/*
|--------------------------------------------------------------------------
| mjml-related constants
|--------------------------------------------------------------------------
*/
const mjmlSelector = [
    'resources/views/emails/*/*.blade.mjml',
    '!resources/views/emails/common/*.blade.mjml'
];
const mjmlOptions = {base: 'resources/views/emails/'};

/*
|--------------------------------------------------------------------------
| mjml: Generate all mjml files
|--------------------------------------------------------------------------
*/
gulp.task('mjml', function () {
    gulp.src(mjmlSelector, mjmlOptions)
        .pipe(mjml())
        .pipe(gulp.dest('resources/views/emails/'))
        .pipe(extReplace('.php'))
        .pipe(gulp.dest('resources/views/emails/'))
});


/*
|--------------------------------------------------------------------------
| mjml-watch: Generate mjml on the fly and use livereload
|--------------------------------------------------------------------------
*/
gulp.task('mjml-watch', ['mjml'], function () {
    livereload.listen();

    return gulp.watch(mjmlSelector, mjmlOptions, function (event) {
        gulp.src(event.path)
            .pipe(mjml())
            .pipe(gulp.dest('resources/views/emails/'))
            .pipe(extReplace('.php'))
            .pipe(gulp.dest('resources/views/emails/'))
            .pipe(livereload());
    });
});