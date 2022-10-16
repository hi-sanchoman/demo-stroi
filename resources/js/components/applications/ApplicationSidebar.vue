<template>
    <div class="border">
        <v-list density="compact" transition="slide-x-transition">
            <v-list-subheader class="d-block">
                Заявки

                <div class="float-right" v-if="currentUser != null && currentUser.roles[0].title == 'PTD Engineer'">
                    <v-btn color="primary" size="x-small" plain @click="openTypeChooser()">
                        <v-icon>mdi-plus</v-icon>
                    </v-btn>
                </div>
            </v-list-subheader>

            <router-link to="/applications?status=all" class="text-decoration-none text-black">
                <v-list-item key="all" value="all" active-color="primary">
                    <v-list-item-title v-text="'Все заявки'"></v-list-item-title>
                </v-list-item>
            </router-link>

            <v-list-item>
                <v-list-item-title v-text="' '"></v-list-item-title>
            </v-list-item>

            <!-- if PTD Engineer -->
            <template v-if="currentUser != null && currentUser.roles[0].title == 'PTD Engineer'">

                <router-link to="/applications?status=draft" class="text-decoration-none text-black">
                    <v-list-item key="draft" value="draft" active-color="primary">
                        <v-list-item-title v-text="'Черновики'"></v-list-item-title>
                    </v-list-item>
                </router-link>

                <!-- <router-link to="/applications?status=in_review" class="text-decoration-none text-black">
                    <v-list-item key="in_review" value="in_review" active-color="primary">
                        <v-list-item-title v-text="'На рассмотрении'"></v-list-item-title>
                    </v-list-item>
                </router-link>

                <router-link to="/applications?status=declined" class="text-decoration-none text-black">
                    <v-list-item key="declined" value="declined" active-color="primary">
                        <v-list-item-title v-text="'Отклоненные'"></v-list-item-title>
                    </v-list-item>
                </router-link>

                <router-link to="/applications?status=in_progress" class="text-decoration-none text-black">
                    <v-list-item key="in_progress" value="in_progress" active-color="primary">
                        <v-list-item-title v-text="'Открытые'"></v-list-item-title>
                    </v-list-item>
                </router-link> -->
            </template>

            <!-- <template v-else-if="currentUser != null && currentUser.roles[0].title == 'Supplier'">
                <router-link to="/applications?status=in_progress_supplier" class="text-decoration-none text-black">
                    <v-list-item key="in_progress_supplier" value="in_progress_supplier" active-color="primary">
                        <v-list-item-title v-text="`Открытые`"></v-list-item-title>
                    </v-list-item>
                </router-link>
            </template>

            <template
                v-else-if="currentUser != null && (currentUser.roles[0].title == 'Economist' || currentUser.roles[0].title == 'Chief Financial Officer')">
                <router-link to="/applications?status=in_progress_economist" class="text-decoration-none text-black">
                    <v-list-item key="in_progress_economist" value="in_progress_economist" active-color="primary">
                        <v-list-item-title v-text="`Открытые`"></v-list-item-title>
                    </v-list-item>
                </router-link>
            </template> -->

            <!-- <template v-else-if="currentUser != null && currentUser.roles[0].title == 'Warehouse Manager'">
                <router-link to="/applications?status=in_progress_warehouse" class="text-decoration-none text-black">
                    <v-list-item key="in_progress_warehouse" value="in_progress_warehouse" active-color="primary">
                        <v-list-item-title v-text="`Открытые`"></v-list-item-title>
                    </v-list-item>
                </router-link>
            </template> -->


            <!-- <template
                v-if="currentUser != null && (currentUser.roles[0].title != 'Warehouse Manager' && currentUser.roles[0].title != 'PTD Engineer')">
                <router-link to="/applications?status=incoming" class="text-decoration-none text-black">
                    <v-list-item key="incoming" value="incoming" active-color="primary">
                        <v-list-item-title v-text="`Входящие`"></v-list-item-title>
                    </v-list-item>
                </router-link>

                <router-link to="/applications?status=declined_by_me" class="text-decoration-none text-black">
                    <v-list-item key="declined_by_me" value="declined_by_me" active-color="primary">
                        <v-list-item-title v-text="`Отклоненные мною`"></v-list-item-title>
                    </v-list-item>
                </router-link>
            </template> -->

            <!-- closed - completed -->
            <template>
                <router-link to="/applications?status=signed" class="text-decoration-none text-black">
                    <v-list-item key="signed" value="signed" active-color="primary">
                        <v-list-item-title v-text="'Мат. исполненные'"></v-list-item-title>
                    </v-list-item>
                </router-link>

                <router-link to="/applications?status=completed" class="text-decoration-none text-black">
                    <v-list-item key="completed" value="completed" active-color="primary">
                        <v-list-item-title v-text="'Закрытые'"></v-list-item-title>
                    </v-list-item>
                </router-link>
            </template>

        </v-list>
    </div>



    <!-- Choose Application Type Dialog -->
    <v-dialog v-model="typeChooserDialog" persistent>
        <v-card class="oks-dialog min-w-5xl w-7xl" style="">
            <v-card-title>
                <span class="text-h5">Выберите тип заявки</span>
            </v-card-title>

            <v-card-text>
                <v-container>
                    <v-item-group v-model="chosenType" mandatory>
                        <v-container>
                            <v-row>
                                <v-col v-for="kind in kinds" :key="kind.id" cols="12" md="4">
                                    <v-item v-slot="{ isSelected, toggle }">
                                        <v-card :color="isSelected ? 'primary' : ''"
                                            class="d-flex align-center oks-type-chooser" dark @click="toggle">
                                            <v-scroll-y-transition>
                                                <div class="text-sm flex-grow-1 text-center">
                                                    {{ isSelected ? 'Выбрано' : kind.name }}
                                                </div>
                                            </v-scroll-y-transition>
                                        </v-card>
                                    </v-item>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-item-group>
                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>

                <v-btn color="default" text @click="typeChooserDialog = false">
                    Отмена
                </v-btn>

                <v-btn color="success" text @click="createApplication()">
                    Создать заявку
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>

</template>

<script>

export default {
    props: ['currentUser'],

    data() {
        return {
            typeChooserDialog: false,
            chosenType: null,
            isSelected: false,
            kinds: [
                { id: 'product', name: 'Заявка на товар' },
                { id: 'equipment', name: 'Заявка на спец. технику' },
                { id: 'service', name: 'Заявка на услугу' },
            ]
        };
    },

    mounted() {
        // console.log()
    },

    methods: {
        openTypeChooser() {
            this.typeChooserDialog = true;
        },

        createApplication() {
            let kind = this.kinds[this.chosenType];
            window.location.href = `/applications/create?kind=${kind.id}`;
        }
    }
}
</script>


<style>
.oks-type-chooser {
    height: 4rem;
}

@media only screen and (min-width: 768px) {
    .oks-type-chooser {
        height: 10rem;
    }
}
</style>