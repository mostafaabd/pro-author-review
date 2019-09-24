import { src, dest } from 'gulp';
import cleanCss from 'gulp-clean-css';
import postcss from 'gulp-postcss';
import rename from 'gulp-rename';
import concat from 'gulp-concat';
import autoprefixer from 'autoprefixer';
import webpack from 'webpack-stream';
import wpPot from "gulp-wp-pot";
import zip from "gulp-zip";
import info from "./package.json";

export const stylesfront = () => {
  return src('assets/css/front/author-review.css')
    .pipe(postcss([ autoprefixer ]))
    .pipe(cleanCss({compatibility:'ie8'}))
    .pipe(rename( { 'suffix': '.min' } ) )
    .pipe(dest('assets/css/front'));
}

export const stylesadmin = () => {
  return src('assets/css/admin/review.css')
    .pipe(postcss([ autoprefixer ]))
    .pipe(cleanCss({compatibility:'ie8'}))
    .pipe(rename( { 'suffix': '.min' } ) )
    .pipe(dest('assets/css/admin'));
}

export const scriptsfront = () => {
  return src(['assets/js/front/jquery.ui.touch-punch.js','assets/js/front/jquery.appear.js',  'assets/js/front/custom-script.js'])
  .pipe(concat('author-review.js'))
  .pipe(dest('assets/js/front/'))
  .pipe(webpack({
    module: {
      rules: [
        {
          test: /\.js$/,
          use: {
            loader: 'babel-loader',
            options: {
              presets: ['@babel/preset-env']
            }
          }
        }
      ]
    },
    mode: 'production',
    devtool: false,
    output: {
      filename: 'author-review.min.js'
    },
  }))
  .pipe(dest('assets/js/front'));
}

export const scriptsadmin = () => {
  return src(['assets/js/admin/jquery.jstepper.js', 'assets/js/admin/custom-script.js'])
  .pipe(concat('author-review-admin.js'))
  .pipe(dest('assets/js/admin/'))
  .pipe(webpack({
    module: {
      rules: [
        {
          test: /\.js$/,
          use: {
            loader: 'babel-loader',
            options: {
              presets: ['@babel/preset-env']
            }
          }
        }
      ]
    },
    mode: 'production',
    devtool: false,
    output: {
      filename: 'author-review-admin.min.js'
    },
  }))
  .pipe(dest('assets/js/admin'));
}

export const pot = () => {
  return src("**/*.php")
  .pipe(
      wpPot({
        domain: "pro-author-review",
        package: info.name
      })
    )
  .pipe(dest(`languages/${info.name}.pot`));
};

export const compress = () => {
  return src([
    "**/*",
    "!node_modules{,/**}",
    "!bundled{,/**}",
    "!src{,/**}",
    "!.babelrc",
    "!.gitignore",
    "!gulpfile.babel.js",
    "!package.json",
    "!package-lock.json",
    ])
    .pipe(zip(`${info.name}.zip`))
    .pipe(dest('../'));
};