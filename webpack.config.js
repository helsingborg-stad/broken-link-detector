const path = require('path');
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');
const WebpackNotifierPlugin = require('webpack-notifier');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const RemoveEmptyScripts = require('webpack-remove-empty-scripts');
const CssMinimizerWebpackPlugin = require('css-minimizer-webpack-plugin');
const autoprefixer = require('autoprefixer');
const {getIfUtils, removeEmpty} = require('webpack-config-utils');
const { ifProduction } = getIfUtils(process.env.NODE_ENV);
const CopyWebpackPlugin = require('copy-webpack-plugin');


module.exports = {
    mode: ifProduction('production', 'development'),

    /**
     * Add your entry files here
     */
    entry: {
        'css/broken-link-detector':              './source/sass/broken-link-detector.scss',
        // 'js/broken-link-detector':               './source/js/broken-link-detector.js',
        'js/mce-broken-link-detector':           './source/mce/mce-broken-link-detector.js'
    },
    
    /**
     * Output settings
     */
    output: {
        filename: '[name].[contenthash].min.js',
        path: path.resolve(__dirname, 'dist'),
        publicPath: '',
        clean: true,
    },
    /**
     * Define external dependencies here
     */
    externals: {
    },
    optimization: {
        removeEmptyChunks: false,
        usedExports: true,
        minimize: false
    },
    module: {
        rules: [
            /**
             * Styles
             */
            {
                test: /\.(sa|sc|c)ss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader',
                        options: {
                            importLoaders: 3, // 0 => no loaders (default); 1 => postcss-loader; 2 => sass-loader
                        },
                    },
                    {
                        loader: 'postcss-loader',
                        options: {
                            postcssOptions: {
                                plugins: [ autoprefixer ],
                            }
                        }
                    },
                    {
                        loader: 'sass-loader',
                        options: {}
                    },
                    'import-glob-loader'
                ],
            },
        ],
    },
    plugins: removeEmpty([
        /**
         * Fix CSS entry chunks generating js file
         */
        new RemoveEmptyScripts(),

        /**
         * Clean dist folder
         */
        new CleanWebpackPlugin(),
        /**
         * Output CSS files
         */
        new MiniCssExtractPlugin({
            filename: '[name].[contenthash].css'
        }),

        new CopyWebpackPlugin({
            patterns: [
                { from: 'source/js/broken-link-detector.js', to: 'js/broken-link-detector.min.js' },
            ],
        }),

        /**
         * Output manifest.json for cache busting
         */
        new WebpackManifestPlugin({
            // Filter manifest items
            filter: function (file) {
                // Don't include source maps
                if (file.path.match(/\.(map)$/)) {
                    return false;
                }
                return true;
            },
            // Custom mapping of manifest item goes here
            map: function (file) {
                // Adjust the manifest keys for the JS files
                if (file.name === 'js/mce-broken-link-detector.min.js') {
                    file.name = 'js/mce-broken-link-detector.js'; // The key in the manifest
                }

                // Fix incorrect key for fonts
                if (
                    file.isAsset &&
                    file.isModuleAsset &&
                    file.path.match(/\.(woff|woff2|eot|ttf|otf)$/)
                ) {
                    const pathParts = file.path.split('.');
                    const nameParts = file.name.split('.');

                    // Compare extensions
                    if (pathParts[pathParts.length - 1] !== nameParts[nameParts.length - 1]) {
                        file.name = pathParts[0].concat('.', pathParts[pathParts.length - 1]);
                    }
                }
                
                return file;
            },
        }),

        /**
         * Enable build OS notifications (when using watch command)
         */
        new WebpackNotifierPlugin({alwaysNotify: true, skipFirstNotification: true}),

        /**
         * Minimize CSS assets
         */
        ifProduction(new CssMinimizerWebpackPlugin({
            minimizerOptions: {
                preset: [
                    "default",
                    {
                        discardComments: { removeAll: true },
                    },
                ],
            },
        }))

    ]).filter(Boolean),
    devtool: 'source-map',
    stats: { children: false }
};