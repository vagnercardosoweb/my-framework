import Vue from 'vue';

import Example from './components/example';

[Example].forEach(component => Vue.component(component.name, component));

new Vue({ el: '#app' });
