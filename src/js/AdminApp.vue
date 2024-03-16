<template>
    <div id="easy-notes_app">
        <div class="easy-notes_main_nav">
            <div class="topnav">
                <router-link v-for="menuItem in topMenus" :key="menuItem.route" :class="['ninja-tab']" :to="{ name: menuItem.route }"
                             active-class="ninja-tab-active" exact>
                    {{ menuItem.title }}
                </router-link>
            </div>

        </div>
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
        setTopmenu() {
            this.topMenus = this.applyFilters('easy-notes_top_level_menu', [
                {
                    route: 'notes',
                    title: 'Notes'
                },
                {
                    route: 'create',
                    title: 'Add Note'
                },
            ])
        }
    },
    mounted() {
        this.setTopmenu();
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