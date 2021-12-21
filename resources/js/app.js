import Vue from 'vue';
import axios from 'axios';
import TheApp from './components/TheApp.vue';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

new Vue({
    render: (h) => h(TheApp),
}).$mount('#app');
