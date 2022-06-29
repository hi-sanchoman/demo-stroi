<template>
    <div>
        <!-- if PTD Engineer -->
        <v-list 
            density="compact" 
            transition="slide-x-transition"
            v-if="currentUser != null && currentUser.roles[0].title == 'PTD Engineer'"
        >
            <v-list-subheader class="d-block">
                Заявки 
                
                <div class="float-right">
                    <router-link :to="{ name: 'applications.create' }" class="text-white text-decoration-none">
                        <v-btn
                            color="primary"
                            size="x-small"
                            plain
                        >
                            <v-icon>mdi-plus</v-icon>
                        </v-btn>
                    </router-link>
                </div>
            </v-list-subheader>
            
            <router-link to="/applications?status=draft" class="text-decoration-none text-black">
                <v-list-item
                    key="draft"
                    value="draft"
                    active-color="primary"
                >
                    <v-list-item-title v-text="'Черновики'"></v-list-item-title>
                </v-list-item>
            </router-link>
            
            <router-link to="/applications?status=in_review" class="text-decoration-none text-black">
                <v-list-item
                    key="in_review"
                    value="in_review"
                    active-color="primary"
                >
                    <v-list-item-title v-text="'На рассмотрении'"></v-list-item-title>
                </v-list-item>
            </router-link>

            <router-link to="/applications?status=declined" class="text-decoration-none text-black">
                <v-list-item
                    key="declined"
                    value="declined"
                    active-color="primary"
                >
                    <v-list-item-title v-text="'Отклоненные'"></v-list-item-title>
                </v-list-item>
            </router-link>

            <router-link to="/applications?status=in_progress" class="text-decoration-none text-black">
                <v-list-item
                    key="in_progress"
                    value="in_progress"
                    active-color="primary"
                >
                    <v-list-item-title v-text="'Открытые'"></v-list-item-title>
                </v-list-item>
            </router-link>
            
            <router-link to="/applications?status=completed" class="text-decoration-none text-black">
                <v-list-item
                    key="completed"
                    value="completed"
                    active-color="primary"
                >
                    <v-list-item-title v-text="'Завершенные'"></v-list-item-title>
                </v-list-item>
            </router-link>
        </v-list>

        <v-list 
            v-else-if="currentUser != null && currentUser.roles[0].title == 'Supplier'"
            density="compact" 
            transition="slide-x-transition"
        >
            <v-list-subheader class="d-block">
                Заявки 
            </v-list-subheader>
            
            <router-link to="/applications?status=in_progress_supplier" class="text-decoration-none text-black">
                <v-list-item
                    key="in_progress_supplier"
                    value="in_progress_supplier"
                    active-color="primary"
                >
                    <v-list-item-title v-text="`Открытые`"></v-list-item-title>
                </v-list-item>
            </router-link>            
        </v-list>

        <v-list 
            v-else-if="currentUser != null && (currentUser.roles[0].title == 'Economist' || currentUser.roles[0].title == 'Chief Financial Officer')"
            density="compact" 
            transition="slide-x-transition"
        >
            <v-list-subheader class="d-block">
                Заявки 
            </v-list-subheader>
            
            <router-link to="/applications?status=in_progress_economist" class="text-decoration-none text-black">
                <v-list-item
                    key="in_progress_economist"
                    value="in_progress_economist"
                    active-color="primary"
                >
                    <v-list-item-title v-text="`Открытые`"></v-list-item-title>
                </v-list-item>
            </router-link>            
        </v-list>

        <v-list 
            v-else-if="currentUser != null && currentUser.roles[0].title == 'Warehouse Manager'"
            density="compact" 
            transition="slide-x-transition"
        >
            <v-list-subheader class="d-block">
                Заявки 
            </v-list-subheader>
            
            <router-link to="/applications?status=in_progress_warehouse" class="text-decoration-none text-black">
                <v-list-item
                    key="in_progress_warehouse"
                    value="in_progress_warehouse"
                    active-color="primary"
                >
                    <v-list-item-title v-text="`Открытые`"></v-list-item-title>
                </v-list-item>
            </router-link>
        </v-list>

        <v-list 
            v-else
            density="compact" 
            transition="slide-x-transition"
        >
            <v-list-subheader class="d-block">
                Заявки 
            </v-list-subheader>
            
            <router-link to="/applications?status=incoming" class="text-decoration-none text-black">
                <v-list-item
                    key="incoming"
                    value="incoming"
                    active-color="primary"
                >
                    <v-list-item-title v-text="`Входящие`"></v-list-item-title>
                </v-list-item>
            </router-link>

            <router-link to="/applications?status=declined_by_me" class="text-decoration-none text-black">
                <v-list-item
                    key="declined_by_me"
                    value="declined_by_me"
                    active-color="primary"
                >
                    <v-list-item-title v-text="`Отклоненные мною`"></v-list-item-title>
                </v-list-item>
            </router-link>
        </v-list>
    </div>
</template>

<script>

export default {
    props: ['currentUser'],

    mounted() {
        console.log('user', this.currentUser)
    }
}
</script>
