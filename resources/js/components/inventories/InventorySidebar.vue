<template>
    <div>
        <v-list 
            density="compact" 
            transition="slide-x-transition"
        >
            <v-list-subheader class="d-block">
                Склад
            </v-list-subheader>
            
            <router-link :to="'/inventories/' + inventory.id + '?status=waiting'" class="text-decoration-none text-black">
                <v-list-item
                    key="waiting"
                    value="waiting"
                    active-color="primary"
                >
                    <v-list-item-title v-text="'Приход'"></v-list-item-title>
                </v-list-item>
            </router-link>
            
            <router-link :to="'/inventories/' + inventory.id + '?status=accepted'" class="text-decoration-none text-black">
                <v-list-item
                    key="accepted"
                    value="accepted"
                    active-color="primary"
                >
                    <v-list-item-title v-text="'Товары'"></v-list-item-title>
                </v-list-item>
            </router-link>

            <!-- <router-link :to="'/inventories/' + inventory.id + '?status=declined'" class="text-decoration-none text-black">
                <v-list-item
                    key="declined"
                    value="declined"
                    active-color="primary"
                >
                    <v-list-item-title v-text="'Отклоненные'"></v-list-item-title>
                </v-list-item>
            </router-link>           -->
            
            <!-- <router-link :to="'/inventories/' + inventory.id + '/history'" class="text-decoration-none text-black">
                <v-list-item
                    key="history"
                    value="history"
                    active-color="primary"
                >
                    <v-list-item-title v-text="'История'"></v-list-item-title>
                </v-list-item>
            </router-link>   -->
        </v-list>
    </div>
</template>

<script>

export default {
    props: ['currentUser', 'inventory'],

    mounted() {
        console.log('user', this.currentUser)
    }
}
</script>
