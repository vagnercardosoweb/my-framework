import 'babel-polyfill';
import jQuery from 'jquery';
import 'bootstrap';
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

if (typeof window !== 'undefined') {
  window.$ = global.$ = jQuery;
  window.jQuery = global.jQuery = jQuery;
}
