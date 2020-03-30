import $ from 'jquery';

global.$ = global.jQuery = $;

import 'popper.js';
import 'bootstrap';
// import 'babel-polyfill';
import './styles/app.scss';

// React components and pages
// import './react/components/example';

// export const curry = (fn, ...args) => (...arg) => fn(...args, ...arg);
//
// export function curryFn(fn, ...args) {
//   return function(...arg) {
//     return fn(...args, ...arg);
//   };
// }
