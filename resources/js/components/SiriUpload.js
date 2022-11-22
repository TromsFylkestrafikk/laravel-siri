import axios from 'axios';

let inputFiles = null;

export default {
    data() {
        return {
            cancelUpload: false,
            currentFilename: null,
            currentFileIndex: 0,
            delayOptions: [0, 0.2, 0.3, 0.5, 1, 2, 5, 10, 30, 60, 120],
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
        submitXml() {
            this.cancelUpload = false;
            const filesInput = this.$refs.xmlFile;
            if (!filesInput.files.length || !this.selectedId) {
                return;
            }
            inputFiles = filesInput.files;
            this.currentFileIndex = 0;
            this.uploadInProgress = true;
            this.fileCount = inputFiles.length;
            this.submitFile(inputFiles[this.currentFileIndex]);
            this.delayedNextSubmit();
        },

        delayedNextSubmit() {
            this.currentFileIndex += 1;
            if (!inputFiles[this.currentFileIndex]) {
                this.reset();
                return;
            }
            const nextFile = inputFiles[this.currentFileIndex];
            setTimeout(() => {
                if (this.cancelUpload) {
                    this.reset();
                    return;
                }
                this.submitFile(nextFile);
                this.delayedNextSubmit();
            }, this.uploadDelay * 1000);
        },

        delayedSubmit(file) {
            if (this.cancelUpload) {
                return;
            }
            this.submitFile(file);
            this.delayedNextSubmit();
        },

        async submitFile(file) {
            this.response = null;
            this.currentFilename = file.name;
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

        reset() {
            this.cancelUpload = false;
            this.uploadInProgress = false;
        }
    },
};
