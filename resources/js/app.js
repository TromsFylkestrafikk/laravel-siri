import Vue from 'vue';
import axios from 'axios';
import TheApp from './components/TheApp';

window.axios = axios;

window.app = new Vue({
    render: (h) => h(TheApp),
}).$mount('#app');
