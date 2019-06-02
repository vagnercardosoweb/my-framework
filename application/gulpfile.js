'use strict';

// Plugins
const fs = require('fs');
const gulp = require('gulp');
const sass = require('gulp-sass');
const postcss = require('gulp-postcss');
const minifycss = require('gulp-minify-css');
const notify = require('gulp-notify');
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
const babel = require('gulp-babel');
const browsersync = require('browser-sync').create();
const plumber = require('gulp-plumber');

// Loader
const config = JSON.parse(
  fs.readFileSync('./resources/assets/config.json', 'UTF8')
) || {};


// Create task css
function taskCss(src, fileName, dest) {
  const includePaths = `${config.path.public}/assets`;
  const notifyMessage = `${dest.replace(config.path.public, '')}/${fileName}.css successfully.`;
  fileName = `${fileName}.css`;

  return gulp
    .src(src)
    .pipe(plumber())
    .pipe(sass({includePaths: includePaths}))
    .pipe(concat(fileName))
    .pipe(postcss([
      require('autoprefixer')(),
      require('css-mqpacker')()
    ]))
    .pipe(minifycss({
      keepBreaks: false,
      target: config.path.public,
      root: config.path.public,
      processImport: true,
      keepSpecialComments: 0
    }))
    .pipe(concat(fileName))
    .pipe(notify(notifyMessage))
    .pipe(gulp.dest(dest))
    .pipe(browsersync.stream());
}


// Create task javascript
function taskJs(src, fileName, dest) {
  const notifyMessage = `${dest.replace(config.path.public, '')}/${fileName}.js successfully.`;
  fileName = `${fileName}.js`;

  return gulp
    .src(src)
    .pipe(plumber())
    .pipe(concat(fileName))
    .pipe(babel({
      presets: [["@babel/env", {"modules": false}]],
      plugins: ["add-module-exports"]
    }))
    .pipe(uglify())
    .pipe(concat(fileName))
    .pipe(notify(notifyMessage))
    .pipe(gulp.dest(dest))
    .pipe(browsersync.stream());
}

// BrowserSync
function browserSync(done) {
  browsersync.init({
    proxy: 'localhost',
    port: 3001,
    files: [
      '**/*.php',
      '**/*.twig',
      '**/*.css',
      '**/*.js',
      '**/*.html'
    ],
    injectChanges: false
  });

  done();
}

// Other tasks
const taskCssError = () => taskCss('./resources/assets/sass/error.scss', 'error', config.path.css);
const taskCssMail = () => taskCss('./resources/assets/sass/mail.scss', 'mail', config.path.css);
const taskCssWeb = () => taskCss(config.web.css, 'app', `${config.path.css}/web`);
const taskJsWeb = () => taskJs(config.web.js, 'app', `${config.path.js}/web`);

// Watch files
function watchAllFiles() {
  gulp.watch('./resources/assets/sass/error.{scss,css}', taskCssError);
  gulp.watch('./resources/assets/sass/mail.{scss,css}', taskCssMail);
  gulp.watch('./resources/assets/sass/web/**/**/**/*.{scss,css}', taskCssWeb);
  gulp.watch('./resources/assets/js/web/**/**/**/*.js', taskJsWeb);
  gulp.watch('./resources/assets/js/plugins/**/**/**/*.js', gulp.parallel(taskJsWeb));
  gulp.watch([
    '../!**!/!*.{php,twig,js,css}',
    '!../!**!/docker',
    '!../!**!/node_modules',
    '!../!**!/bower_componets',
    '!../!**!/vendor'
  ], browsersync.reload);
}

// Export tasks
exports.taskCssError = taskCssError;
exports.taskCssMail = taskCssMail;
exports.taskCssWeb = taskCssWeb;
exports.taskJsWeb = taskJsWeb;
exports.watchAllFiles = gulp.parallel(watchAllFiles, browserSync);
exports.browserSync = browserSync;

// Export default task
exports.default = gulp.series(
  gulp.parallel(taskCssError, taskCssMail),
  gulp.parallel(taskCssWeb, taskJsWeb),
  gulp.parallel(watchAllFiles)
);
