(function(){
    'use strict';

    var gulp   = require('gulp');
    var notify = require('gulp-notify');
    var sass   = require('gulp-sass');

    gulp.task('sass', function() {
        gulp.src('public_html/app/style/app.scss')
            .pipe(sass())
            .pipe(notify('SASS changed'))
            .pipe(gulp.dest('public_html'));

    });

    gulp.task('watch', ['sass'], function() {
        gulp.watch('public_html/app/style/**/*.scss', ['sass']);
    });
})();
