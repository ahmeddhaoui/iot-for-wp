const MiniCssExtractPlugin = require('mini-css-extract-plugin');

var path = require('path');

const outputPath = '../assets';

const entryPoints = {
    admin: ['./js/admin.js', './css/admin.css'],
    app: ['./js/app.js', './css/style.css'],
};

module.exports = {
    entry: entryPoints,
    output: {
        path: path.resolve(__dirname, outputPath),
        filename: '[name].min.js',
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: '[name].min.css',
        }),
    ],
    module: {
        rules: [
            {
                test: /\.s?[c]ss$/i,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader'
                ],
            }
        ]
    },
};