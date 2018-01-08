/**
 * Copyright 2016 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the 'License');
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an 'AS IS' BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

var webpack = require('webpack');

var PROD = process.argv.indexOf('-p') !== -1

var precss = require('precss');
var calc = require("postcss-calc")
var autoprefixer = require('autoprefixer');
const CopyWebpackPlugin = require('copy-webpack-plugin');
// const NodemonBrowsersyncPlugin = require('nodemon-browsersync-webpack-plugin');

const cpy = new CopyWebpackPlugin([
            { from: 'src/js/static', to: 'public/javascript' }
        ], {
            ignore: [
                // // Doesn't copy any files with a txt extension    
                // '*.txt',
                
                // // Doesn't copy any file, even if they start with a dot
                // '**/*',

                // // Doesn't copy any file, except if they start with a dot
                // { glob: '**/*', dot: false }
            ],

            // By default, we only copy modified files during
            // a watch or webpack-dev-server build. Setting this
            // to `true` copies all files.
            copyUnmodified: true
        })

module.exports = {
	'context': __dirname,
	entry: {
		 card:'./src/card.js',
		 main:'./src/main.js'
	},
	output: {
		filename: './public/javascript/[name].js',
		chunkFilename: './public/javascript/build/[id].js',
		sourceMapFilename : '[file].map',
	    // hotUpdateChunkFilename: 'tmp/hot/hot-update.js',
	    // hotUpdateMainFilename: 'tmp/hot/hot-update.json'
	},
	resolve: {
		root: __dirname,
		modulesDirectories : ['node_modules'],
	},
	plugins: PROD ? [
	    new webpack.optimize.UglifyJsPlugin({minimize: true}),
	    cpy
	  ] : [
	  	cpy
	  	// new webpack.HotModuleReplacementPlugin(),
    //     new NodemonBrowsersyncPlugin({
    //         script: 'bin/www',
    //         ignore: [
    //         	"package.json",
    //         	"webpack.config.js",
    //             "src/css/main.scss",
    //             "public/*"
    //         ],
    //         ext: 'js json',
    //         verbose: true
    //     }, {
    //         proxy: 'localhost:3000'
    //     })
        ],
	module: {
		loaders: [
			{
				test:   /\.css$/,
				loader: 'style-loader!css-loader!postcss-loader'
           	},
			{
				test: /\.json$/,
				loader: 'json-loader'
			},
			{
				test: /\.js$/,
				exclude: /(node_modules|Tone\.js|static)/,
				loader: 'babel', // 'babel-loader' is also a legal name to reference
				query: {
					presets: ['es2015'],
					"plugins": ["es6-promise"]
				}
			},
			{
				test: /\.(png|gif|jpg|svg)$/,
				loader: 'url-loader',
			}
		]
	},
	postcss: function () {
        return [precss, autoprefixer, calc];
    },
    devtool: PROD ? '' : '#eval-source-map'
};