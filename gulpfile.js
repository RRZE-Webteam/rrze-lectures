'use strict';

const
    {src, dest, watch, series, parallel} = require('gulp'),
    sass = require('gulp-sass')(require('sass')),
    postcss = require('gulp-postcss'),
    autoprefixer = require('autoprefixer'),
    uglify = require('gulp-uglify'),
    babel = require('gulp-babel'),
    bump = require('gulp-bump'),
    semver = require('semver'),
    info = require('./package.json'),
    wpPot = require('gulp-wp-pot'),
    touch = require('gulp-touch-cmd')
;


function css() {
    var plugins = [
        autoprefixer()
    ];
    return src([info.source.sass + 'rrze-lectures.scss'])
        .pipe(sass().on('error', sass.logError))
        .pipe(postcss(plugins))
        .pipe(dest(info.target.css))
	.pipe(touch());
}
function cssdev() {
    var plugins = [
        autoprefixer()
    ];
    return src([info.source.sass + 'rrze-lectures.scss'])
        .pipe(sass().on('error', sass.logError))
        .pipe(postcss(plugins))
        .pipe(dest(info.target.css))
	.pipe(touch());
}

function js() {
    return src(['./src/js/*.js', '!./src/js/tinymce-shortcodes.js']) // because uglyfy deletes (function() { which is mandatory otherwise editor is undefined BK 09.06.2021
	.pipe(uglify())
	.pipe(dest('./js'))
	.pipe(touch());
}

function patchPackageVersion() {
    var newVer = semver.inc(info.version, 'patch');
    return src(['./package.json', './' + info.main])
        .pipe(bump({
            version: newVer
        }))
        .pipe(dest('./'))
	.pipe(touch());
};
function prereleasePackageVersion() {
    var newVer = semver.inc(info.version, 'prerelease');
    return src(['./package.json', './' + info.main])
        .pipe(bump({
            version: newVer
        }))
	.pipe(dest('./'))
	.pipe(touch());;
};

function updatepot()  {
  return src("**/*.php")
  .pipe(
      wpPot({
        domain: info.textdomain,
        package: info.name,
	team: info.author.name,
	bugReport: info.repository.issues,
	ignoreTemplateNameHeader: true
 
      })
    )
  .pipe(dest(`languages/${info.textdomain}.pot`))
  .pipe(touch());
};


function startWatch() {
    watch('./src/sass/*.scss', css);
    watch('./src/js/*.js', js);
}

exports.css = css;
exports.js = js;
exports.dev = series(js, cssdev, prereleasePackageVersion);
exports.build = series(js, css, patchPackageVersion);
exports.pot = updatepot;

exports.default = startWatch;
