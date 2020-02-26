import Vue from 'vue';

import TestComponent from './components/Test';

[TestComponent].forEach(component => Vue.component(component.name, component));

new Vue({ el: '#app' });
