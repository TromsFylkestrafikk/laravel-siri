import axios from 'axios';

export default {
    data() {
        return {
            cancelUpload: false,
            currentFilename: null,
            currentFileIndex: 0,
            delayOptions: [0, 1, 2, 5, 10, 30, 60, 120],
            fileCount: 0,
            response: null,
            selectedId: null,
            subscriptions: null,
            uploadDelay: 1,
            uploadInProgress: false,
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
            const filesInput = this.$refs.xmlFile;
            if (!filesInput.files.length || !this.selectedId) {
                return;
            }
            const { files } = filesInput;
            this.uploadInProgress = true;
            this.fileCount = files.length;
            for (let idx = 0; idx < files.length; idx += 1) {
                if (idx > 0) {
                    await this.promiseDelay();
                }
                if (this.cancelUpload) {
                    console.log('Upload was cancelled');
                    this.cancelUpload = false;
                    break;
                }
                console.log(`Processing file ${files[idx].name}`);
                /* eslint no-await-in-loop: off */
                await this.submitFile(files[idx]);
            }
            this.uploadInProgress = false;
        },

        async submitFile(file) {
            const data = await file.text();
            const sub = this.subscriptions[this.selectedId];
            return axios({
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

        promiseDelay() {
            return new Promise((resolve) => {
                setTimeout(resolve, this.uploadDelay * 1000);
            });
        },
    },
};
