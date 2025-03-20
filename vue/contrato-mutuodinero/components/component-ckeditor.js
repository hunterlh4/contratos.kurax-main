Vue.component("component-ckeditor", {
    template: `
    <div>
        <textarea ref="editor"></textarea>
    </div>
    `,
    props: ["value"],
    data() {
        return {
            editor: null,
        };
    },
    mounted() {
        ClassicEditor
            .create(this.$refs.editor)
            .then(editor => {
                this.editor = editor;
                editor.model.document.on('change:data', () => {
                    this.$emit('input', editor.getData());
                });
                editor.setData(this.value);
            })
            .catch(error => {
                console.error(error);
            });
    },
    watch: {
        value(newValue) {
            if (this.editor && newValue !== this.editor.getData()) {
                this.editor.setData(newValue);
            }
        }
    },
    beforeDestroy() {
        if (this.editor) {
            this.editor.destroy();
        }
    }
});