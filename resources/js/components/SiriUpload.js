import axios from 'axios';

export default {
    data() {
        return {
            subscriptions: null,
            selectedId: null,
            response: null,
        };
    },

    mounted() {
        axios.get(route('siri.dev.subscriptions')).then((result) => {
            this.subscriptions = result.data.subscriptions;
        });
    },

    methods: {
        async submitXml() {
            this.response = null;
            const file = this.$refs.xmlFile;
            if (!file.files.length || !this.selectedId) {
                return;
            }
            const data = await file.files[0].text();
            const sub = this.subscriptions[this.selectedId];
            axios({
                method: 'post',
                url: route('siri.consume', [sub.channel, sub.subscription_ref]),
                data,
                headers: { 'Content-Type': 'application/xml' },
            }).then((response) => {
                this.response = response.request;
            }).catch((error) => {
                this.response = error.request;
            });
        },
    },
};
