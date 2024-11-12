const path = require('path');
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');
const WebpackNotifierPlugin = require('webpack-notifier');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const RemoveEmptyScripts = require('webpack-remove-empty-scripts');
const CssMinimizerWebpackPlugin = require('css-minimizer-webpack-plugin');
const autoprefixer = require('autoprefixer');
const { getIfUtils, removeEmpty } = require('webpack-config-utils');
const { ifProduction } = getIfUtils(process.env.NODE_ENV);

module.exports = {
    mode: ifProduction('production', 'development'),

    /**
     * Add your entry files here
     */
    entry: {
        'css/broken-link-detector': './source/sass/broken-link-detector.scss',
        'js/context-detector': './source/js/context-detector.ts',
        'js/editor-highlight': './source/js/editor-highlight.ts'
    },

    /**
     * Output settings
     */
    output: {
        filename: '[name].[contenthash].js',
        path: path.resolve(__dirname, 'dist'),
        publicPath: '',
    },

    /**
     * Define external dependencies here
     */
    externals: {},

    module: {
        rules: [
            /**
             * TypeScript and JavaScript
             */
            {
                test: /\.tsx?$/, // TypeScript and TSX file handling
                use: 'ts-loader',
                exclude: /node_modules/,
            },
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
                                plugins: [autoprefixer],
                            },
                        },
                    },
                    {
                        loader: 'sass-loader',
                        options: {},
                    },
                    'import-glob-loader',
                ],
            },
        ],
    },

    /**
     * Add TypeScript resolution
     */
    resolve: {
        extensions: ['.tsx', '.ts', '.js'], // Resolve .ts and .tsx in addition to .js
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
            filename: '[name].[contenthash].css',
        }),

        /**
         * Output manifest.json for cache busting
         */
        new WebpackManifestPlugin({
            filter: function (file) {
                if (file.path.match(/\.(map)$/)) {
                    return false;
                }
                return true;
            },
            map: function (file) {
                if (
                    file.isAsset &&
                    file.isModuleAsset &&
                    file.path.match(/\.(woff|woff2|eot|ttf|otf)$/)
                ) {
                    const pathParts = file.path.split('.');
                    const nameParts = file.name.split('.');

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
        new WebpackNotifierPlugin({ alwaysNotify: true, skipFirstNotification: true }),

        /**
         * Minimize CSS assets
         */
        ifProduction(
            new CssMinimizerWebpackPlugin({
                minimizerOptions: {
                    preset: [
                        'default',
                        {
                            discardComments: { removeAll: true },
                        },
                    ],
                },
            })
        ),
    ]).filter(Boolean),

    devtool: 'source-map',
    stats: { children: false },
};