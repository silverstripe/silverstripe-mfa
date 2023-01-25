const Path = require('path');
const { JavascriptWebpackConfig, CssWebpackConfig } = require('@silverstripe/webpack-config');
const CopyWebpackPlugin = require('copy-webpack-plugin');

const ENV = process.env.NODE_ENV;
const PATHS = {
  MODULES: 'node_modules',
  ROOT: Path.resolve(),
  SRC: Path.resolve('client/src'),
  DIST: Path.resolve('client/dist'),
};

// Frontend JS bundle
const frontendJsConfig = new JavascriptWebpackConfig('js-frontend', PATHS, 'silverstripe/mfa')
  .setEntry({
    bundle: `${PATHS.SRC}/bundles/bundle.js`,
  })
  .mergeConfig({
    plugins: [
      new CopyWebpackPlugin({
        patterns: [
          {
            from: `${PATHS.SRC}/images`,
            to: `${PATHS.DIST}/images`,
          },
          {
            from: `${PATHS.MODULES}/@silverstripe/react-injector/dist/injector.js`,
            to: `${PATHS.DIST}/js`,
          },
        ]
      }),
    ],
  })
  .getConfig();

// Only include a single "external" which is actually addedto the DOM separately
// Don't include any other externals as this will be used on the frontend
frontendJsConfig.externals = {
  'lib/Injector': 'Injector',
};

const config = [
  frontendJsConfig,
  // Main JS bundle
  new JavascriptWebpackConfig('js-cms', PATHS, 'silverstripe/mfa')
    .setEntry({
      'bundle-cms': `${PATHS.SRC}/bundles/bundle-cms.js`,
    })
    .getConfig(),
  // sass to css
  new CssWebpackConfig('css', PATHS)
    .setEntry({
      bundle: `${PATHS.SRC}/bundles/bundle.scss`,
      'bundle-cms': `${PATHS.SRC}/bundles/bundle-cms.scss`,
    })
    .getConfig(),
];

// Use WEBPACK_CHILD=js or WEBPACK_CHILD=css env var to run a single config
module.exports = (process.env.WEBPACK_CHILD)
  ? config.find((entry) => entry.name === process.env.WEBPACK_CHILD)
  : config;
