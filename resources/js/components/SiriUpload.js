import axios from 'axios';

export default {
    data() {
        return {
            subscriptions: null,
            selectedId: null,
        };
    },

    mounted() {
        axios.get('/api/siri/devel/subscriptions').then((result) => {
            const subs = {};
            result.data.subscriptions.forEach((sub) => {
                subs[sub.id] = sub;
            });
            this.subscriptions = subs;
        });
    },

    methods: {
        async submitXml() {
            const file = this.$refs.xmlFile;
            if (!file.files.length || !this.selectedId) {
                return;
            }
            const xml = await file.files[0].text();
            const sub = this.subscriptions[this.selectedId];
            const url = `/siri/consume/${sub.channel}/${sub.id}`;
            await axios.post(url, xml);
        },
    },
};
