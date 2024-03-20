<template>
    <div id="easy-notes_app">
        <router-view></router-view>
    </div>
</template>

<script>
export default {
    name: 'AdminApp',
    data() {
        return {
            topMenus: []
        }
    },
    methods: {

    },
    mounted() {
    }
}
</script>
<style lang="scss">
#wpbody-content {
    padding: 0 !important;
}

#easy-notes_app {
    margin-left: -20px;
}

#wpfooter {
    display: none;
}

</style>