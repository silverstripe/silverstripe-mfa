import es6promise from 'es6-promise';

es6promise.polyfill();

require('babel-polyfill');
require('../legacy');
require('../boot/cms');
