<template>
    <div>
        <h1>Notes</h1>
    </div>
</template>
<script>
export default {
    name: 'Dashboard',
    data() {
        return {
            vueJs: 'https://vuejs.org/'
        }
    },
    methods: {
        reDirrect() {
            window.open(this.vueJs, "_blank");
        }
    }
}
</script>

