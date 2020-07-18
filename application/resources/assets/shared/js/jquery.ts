import * as $ from 'jquery';

declare global {
  export interface Window {
    $: JQueryStatic;
    jQuery: JQueryStatic;
  }

  namespace NodeJS {
    export interface Global {
      $: JQueryStatic;
      jQuery: JQueryStatic;
    }
  }
}

if (typeof window !== 'undefined') {
  (<any>window).$ = (<any>global).$ = $;
  (<any>window).jQuery = (<any>global).jQuery = $;
}
