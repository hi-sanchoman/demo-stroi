<template>
    <div>
        <v-container>
            <!-- <v-row no-gutters>
                <v-col cols="12">
                    <h1 class="w-full text-left">Заявки</h1>
                </v-col>
            </v-row> -->

            <v-row no-gutters class="mt-10">
                <v-col cols="2" class="border px-5 py-5">
                    <ApplicationSidebar v-if="currentUser != null" :currentUser="currentUser" />
                </v-col>

                <v-col cols="10" class="pl-5">
                    <div v-if="errors">
                        <div v-for="(v, k) in errors" :key="k" class="bg-red-500 text-white rounded font-bold mb-4 shadow-lg py-2 px-4 pr-0">
                            <p v-for="error in v" :key="error" class="text-sm">
                                    {{ error }}
                            </p>
                        </div>
                    </div>

                    <v-form class="space-y-6">
                        <v-row
                            v-if="application != null && application.status != 'draft'"
                            class="mb-5"
                        >
                            <v-col cols="12">
                                <strong>Заявка №{{ application.id }}</strong><br>
                                Дата заявки: {{ application.issued_at }}
                            </v-col>
                        </v-row>

                        <multiselect 
                            v-model="form.construction" 
                            :options="constructions" 
                            placeholder="Укажите строительный объект" 
                            :disabled="application != null && application.status != 'draft'"
                            label="name" 
                            track-by="name">
                        </multiselect>

                        <v-row 
                            v-if="application != null && application.status == 'draft'"
                            class="mt-5"
                        >
                            <v-col cols="12">Добавить товар к заявке:</v-col>
                        </v-row>

                        <v-row v-if="application != null && application.status == 'draft'">
                            <v-col cols="3">
                                <multiselect v-model="current.category" :options="categories" placeholder="Укажите категорию" label="name" track-by="name"></multiselect>
                            </v-col>

                            <v-col cols="3">
                                <multiselect v-model="current.product" :options="options" placeholder="Укажите товар" label="name" track-by="name"></multiselect>
                            </v-col>

                            <v-col cols="2">
                                <v-text-field
                                    v-model="current.quantity"
                                    label="Количество"
                                    @keyup.enter="addProduct()"
                                    variant="underlined"
                                    required
                                    density="comfortable"
                                    type="number"
                                ></v-text-field>
                            </v-col>

                            <v-col cols="2">
                                <v-textarea
                                    v-if="current.isAddNotes"
                                    outlined
                                    v-model="current.notes"
                                    label="Примечание"
                                    variant="underlined"
                                    density="comfortable"
                                    @keyup.enter="addProduct()"
                                ></v-textarea>
                                <v-btn
                                    v-if="!current.isAddNotes"
                                    color=""
                                    size="small"
                                    @click="showNotes()"
                                >
                                    + примечание
                                </v-btn>
                            </v-col>
                            
                            <v-col cols="2">
                                <v-btn 
                                    size="small"
                                    color="primary"
                                    @click="addProduct()"
                                >
                                    <v-icon>mdi-plus</v-icon>
                                </v-btn>
                            </v-col>

                            
                        </v-row>

                        <v-table>
                            <thead>
                                <tr>
                                    <th>№</th>
                                    <th>Статья расходов</th>
                                    <th>Наименование ресурсов</th>
                                    <th>Ед. изм.</th>
                                    <th>Кол-во</th>
                                    <!-- <th>Цена</th>
                                    <th>Сумма</th>
                                    <th>Компания</th> -->
                                    <th>Примечание</th>
                                    <!-- <th v-if="isCanPrepareQuantity()">Подготовлено</th> -->
                                    <!-- <th v-if="isCanReceiveQuantity()">Получено</th> -->
                                    <!-- <th>Файлы</th> -->
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>
                                <template v-for="(item, index) in products" :key="item.id">
                                    <tr>
                                        <td>{{ (index + 1) }}</td>
                                        <td>{{ item.category.name }}</td>
                                        <td>{{ item.product.name }}</td>
                                        <td>{{ item.product.unit }}</td>
                                        <td :class="application.status == 'draft' ? 'd-flex mt-3' : ''">
                                            <v-text-field
                                                v-model="products[index].quantity"
                                                type="number"
                                                variant="plain"
                                                density="compact"
                                                v-if="application.status == 'draft'"
                                            >
                                            </v-text-field>

                                            <span v-if="!isPTDEngineer()">
                                                {{ products[index].prepared }} / 
                                            </span>
                                            <span v-if="application.status != 'draft'">
                                                {{ products[index].quantity }}
                                            </span>
                                        </td>
                                        <!-- <td>
                                            <v-text-field
                                                v-model="products[index].price"
                                                type="number"
                                                density="compact"
                                                class="w-10"
                                                variant="underlined"
                                                style="width: 50px"
                                            >
                                            </v-text-field>
                                        </td> -->

                                        <!-- <td>
                                            {{ countSum(products[index]) }}
                                        </td> -->

                                        <!-- <td class="w-36">
                                            <v-text-field
                                                v-model="products[index].company"
                                                type="text"
                                                density="compact"
                                                class="w-10"
                                                variant="underlined"
                                                style="width: 50px"
                                            >
                                            </v-text-field>
                                        </td> -->

                                        
                                        <!-- <td v-if="isCanReceiveQuantity()" class="">
                                            <span>{{ item.delivered }}</span>    

                                            <v-btn
                                                v-if="item.delivered != item.quantity"
                                                size="x-small"
                                                color="primary"
                                                class="ml-3"
                                                @click="showReceiveDialog(item)"
                                            >
                                                <v-icon>mdi-plus</v-icon>
                                            </v-btn>
                                        </td>  -->
                                        
                                        <td>{{ item.notes }}</td>
                                        
                                        <!-- <td v-if="isCanPrepareQuantity()" class="d-flex mt-3 justify-center" s>
                                            <v-text-field
                                                v-model="products[index].toBePrepared"
                                                type="number"
                                                density="compact"
                                                variant="underlined"
                                                class="w-10"
                                                style="width: 50px"
                                            >
                                            </v-text-field>

                                            <v-btn
                                                size="x-small"
                                                color="green"
                                                class="ml-1 mt-2"
                                                @click="prepareQuantity(item)"
                                            >
                                                <v-icon>mdi-check</v-icon>
                                            </v-btn>
                                        </td>   -->

                                        <!-- <td class="d-flex">
                                            <input
                                                v-if="application.status == 'in_progress'"
                                                type="file"
                                                :ref="'doc_' + item.id"
                                                class="
                                                    w-full
                                                    px-4
                                                    py-2
                                                    mt-2
                                                    border
                                                    rounded-md
                                                    focus:outline-none
                                                    focus:ring-1
                                                    focus:ring-blue-600
                                                "
                                            />

                                            <v-btn
                                                v-if="application.status == 'in_progress'"
                                                size="x-small"
                                                color="primary"
                                                class="ml-3"
                                                @click="uploadFile(item)"
                                            >
                                                Загрузить
                                            </v-btn>
                                        </td> -->
                                        <td>
                                            <v-btn 
                                                v-if="application.status == 'draft'"
                                                size="small"
                                                color="error"
                                                @click="deleteProduct(item)"
                                            >
                                                <v-icon>mdi-close</v-icon>
                                            </v-btn>

                                            <v-btn                                                 
                                                v-if="application.status == 'in_progress' && isSupplier()"
                                                @click="addOffer(item.id)"
                                                color="info"
                                                size="small"
                                            >
                                                + компания
                                            </v-btn>

                                            <v-btn                                                 
                                                v-if="isWarehouseManager() && products[index].quantity != products[index].prepared"
                                                @click="showAcceptProduct(item)"
                                                color="info"
                                                size="small"
                                            >
                                                принять товар
                                            </v-btn>
                                        </td>
                                    </tr>

                                    <!-- <tr v-if="isWarehouseManager() && incoming(item.inventory_applications).length > 0" class="bg-slate-100">
                                        <td colspan="9" class="border-none">
                                            <v-table class="mt-4 mb-8 mx-8 border" style="overflow: visible;">
                                                <thead>
                                                    <tr>
                                                        <th>Статья расходов</th>
                                                        <th>Наименование ресурса</th>
                                                        <th>Ед. изм.</th>
                                                        <th>Кол-во</th>
                                                        <th>Управление</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="it in incoming(item.inventory_applications)" :key="it.id">
                                                        <td>{{ it.application_product.category.name }}</td>
                                                        <td>{{ it.application_product.product.name }}</td>
                                                        <td>{{ it.application_product.product.unit }}</td>
                                                        <td>{{ it.prepared }}</td>
                                                        <td>
                                                            <v-btn class="mr-3" color="success" size="small" @click="acceptProduct(it)">Принять</v-btn>
                                                            <v-btn color="error" size="small" @click="showDeclineProduct(it)">Отклонить</v-btn>
                                                        </td>
                                                    </tr>
                                                </tbody>  
                                            </v-table>
                                        </td>
                                    </tr> -->

                                    <tr v-if="!isWarehouseManager() && item.offers != null && item.offers.length > 0 && (application.status == 'in_progress' || application.status == 'in_review')" class="bg-slate-100">
                                        <td colspan="9" class="border-none">
                                            <v-table class="mt-4 mb-8 mx-8 border">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 25%">Название компании</th>
                                                        <th style="width: 10%">Кол-во</th>
                                                        <th style="width: 10%">Цена за ед.</th>
                                                        <th>Общая сумма</th>
                                                        <th>Счет на оплату</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="offer in item.offers" :key="offer.id">
                                                        <td class="">
                                                            <!-- <v-text-field
                                                                v-if="offer.status == 'draft' && isEditable()"
                                                                v-model="offer.name"
                                                                type="text"
                                                                variant="underlined"
                                                                required
                                                            ></v-text-field> -->
                                                            <div class="flex">
                                                                <multiselect  
                                                                    v-if="offer.status == 'draft' && isEditable()"
                                                                    v-model="offer.company" :options="companies" placeholder="Укажите компанию" label="name" track-by="id">
                                                                </multiselect>

                                                                <v-btn
                                                                    v-if="offer.status == 'draft' && isEditable()"
                                                                    class="mt-1"
                                                                    color="primary"
                                                                    size="x-small"
                                                                    plain
                                                                    @click="showAddCompanyDialog()"
                                                                >
                                                                    Добавить компанию
                                                                </v-btn>
                                                            </div>
                                                            
                                                            <span v-if="!isEditable() && offer != null && offer.company != null">{{ offer.company.name }}</span>
                                                        </td>
                                                        <td>
                                                            <v-text-field
                                                                v-if="offer.status == 'draft' && isEditable()"
                                                                @change="offerHasChanged($event, offer)"
                                                                v-model="offer.quantity"
                                                                label=""
                                                                type="number"
                                                                variant="underlined"
                                                                required
                                                            ></v-text-field>
                                                            <span v-if="!isEditable()">{{ offer.quantity }}</span>
                                                        </td>
                                                        <td>
                                                            <v-text-field
                                                                v-if="offer.status == 'draft' && isEditable()"
                                                                v-model="offer.price"
                                                                label=""
                                                                type="number"
                                                                variant="underlined"
                                                                required
                                                            ></v-text-field>
                                                            <span v-if="!isEditable()">{{ offer.price }} тг</span>    
                                                        </td>
                                                        <td>
                                                            {{ offer.price * offer.quantity }} тг
                                                        </td>
                                                        
                                                        <td>
                                                            <span v-if="offer.file != null">
                                                                <a class="px-2 py-2 mr-2 border text-black text-decoration-none hover:bg-slate-100 cursor-pointer" target="_blank" :href="'/uploads/' + offer.file">Просмотр</a>   
                                                            </span>   

                                                            <input v-if="isEditable()" type="file" id="file" v-on:change="handleFileUpload(offer, $event)"/>                                                             
                                                        </td>

                                                        <td>
                                                            <v-btn 
                                                                v-if="offer.status == 'draft' && isEditable()"
                                                                :id="'offer_' + offer.id"
                                                                @click="updateOffer(offer)"
                                                                color="success"
                                                                size="small"
                                                                class="mr-2"
                                                            >
                                                                Сохранить
                                                            </v-btn>

                                                            <v-btn @click="deleteOffer(offer.id, item.id)"
                                                                color="error"
                                                                size="small"
                                                                v-if="offer.status == 'draft' && isEditable()"
                                                            >
                                                                Удалить
                                                            </v-btn>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </v-table>
                                        </td>
                                    </tr>  
                                       
                                </template>
                            </tbody>
                        </v-table>

                        <v-btn
                            v-if="form.construction != null && products.length > 0 && isPTDEngineer() && application.status == 'draft'"
                            class="mt-5"
                            @click="updateApplication"
                            color="primary"
                        >
                            Сохранить
                        </v-btn>
                    </v-form>

                    <v-container class="mt-10" v-if="application != null">
                        <v-row 
                            v-if="currentUser != null && application.status == 'declined' && application.owner_id == currentUser.id"
                            class="my-5"
                            no-gutters
                        >
                            <v-col cols="12">
                                <v-btn
                                    size="large"
                                    color="primary"
                                    @click="makeApplicationEditable()"
                                    class="ml-5"
                                >
                                    Внести корректировки в заявку
                                </v-btn>
                            </v-col>
                        </v-row>
                        
                        <v-row no-gutters v-if="!isWarehouseManager()">
                            <v-col cols="12">
                                <v-expansion-panels v-model="showPanels">
                                    <v-expansion-panel value="sign">
                                        <v-expansion-panel-title>
                                            Подписание заявки
                                        </v-expansion-panel-title>

                                        <v-expansion-panel-text>
                                            <v-list-item
                                                v-for="item in application.application_application_statuses" :key="item.id"
                                            >
                                                <v-list-item-header>
                                                    <v-list-item-title>
                                                        <strong>{{ item.application_path.id }}. {{ item.application_path.position }}</strong> - {{ item.application_path.responsible.name }}  

                                                        <!-- declined -->
                                                        <span v-if="item.status == 'declined'">
                                                            <v-chip
                                                                class="ma-2"
                                                                color="red"
                                                                text-color="white"
                                                            >
                                                                Отклонено
                                                            </v-chip>

                                                            <span>{{ item.declined_reason }}</span> -    
                                                            <span>{{ item.updated_at }}</span>
                                                        </span>
                                                        <!-- accepted -->
                                                        <span v-else-if="item.status == 'accepted'">
                                                            <v-chip
                                                                class="ma-2"
                                                                color="green"
                                                                text-color="white"
                                                            >
                                                                Одобрено
                                                            </v-chip>

                                                            <span>{{ item.updated_at }}</span>
                                                        </span>
                                                        <!-- incoming -->
                                                        <span v-else-if="currentUser != null && item.status == 'incoming' && item.application_path.responsible.id == currentUser.id">
                                                            <v-btn
                                                                size="small"
                                                                color="success"
                                                                @click="signApplication(item)"
                                                                class="ml-5"
                                                            >
                                                                Подписать
                                                            </v-btn>

                                                            <v-btn
                                                                v-if="item.application_path.responsible.id != application.owner_id"
                                                                size="small"
                                                                color="error"
                                                                @click="showDecline(item)"
                                                                class="ml-5"
                                                            >
                                                                Отклонить
                                                            </v-btn>
                                                        </span>

                                                    </v-list-item-title>
                                                </v-list-item-header>
                                            </v-list-item>
                                        </v-expansion-panel-text>
                                    </v-expansion-panel>
                                </v-expansion-panels>
                            </v-col>
                        </v-row>
                    </v-container>

                </v-col>
            </v-row>
        </v-container>



        <!-- Snackbar -->
        <v-snackbar
            v-model="snackbar.status"
            :timeout="snackbar.timeout"
        >
            {{ snackbar.text }}

            <template v-slot:actions>
                <v-btn
                    color="blue"
                    variant="text"
                    @click="snackbar.status = false"
                >
                    Закрыть
                </v-btn>
            </template>
        </v-snackbar>
        

        <!-- Decline dialog -->
        <v-dialog
            v-model="declineDialog"
            persistent
        >
            <v-card>
                <v-card-title>
                    <span class="text-h5">Отклонить заявку?</span>
                </v-card-title>

                <v-card-text>
                    <v-container>
                        <v-row>
                            <v-col
                                cols="12"
                            >
                                <v-textarea
                                    v-model="declinedReason"
                                    label="Причина отклонения*"
                                    required
                                ></v-textarea>
                            </v-col>
                        </v-row>
                    </v-container>
                    <small>* обязательные поля</small>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    
                    <v-btn
                        color="blue-darken-1"
                        text
                        @click="declineDialog = false"
                    >
                        Отмена
                    </v-btn>

                    <v-btn
                        color="red-darken-1"
                        text
                        @click="declineApplication()"
                    >
                        Отклонить
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Decline Product dialog -->
        <v-dialog
            v-model="declineProductDialog"
            persistent
        >
            <v-card>
                <v-card-title>
                    <span class="text-h5">Отклонить приход?</span>
                </v-card-title>

                <v-card-text>
                    <v-container>
                        <v-row>
                            <v-col
                                cols="12"
                            >
                                <v-textarea
                                    v-model="declinedProductReason"
                                    label="Причина отклонения*"
                                    required
                                ></v-textarea>
                            </v-col>
                        </v-row>
                    </v-container>
                    <small>* обязательные поля</small>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    
                    <v-btn
                        color="blue-darken-1"
                        text
                        @click="declineProductDialog = false"
                    >
                        Отмена
                    </v-btn>

                    <v-btn
                        color="red-darken-1"
                        text
                        @click="declineProduct()"
                    >
                        Отклонить
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog
            v-model="receiveDialog"
            persistent
        >
            <v-card>
                <v-card-title>
                    <span class="text-h5">Получение материально-товарных ценностей</span>
                </v-card-title>

                <v-card-text>
                    <v-container>
                        <v-row>
                            <v-col
                                cols="12"
                            >
                                <v-text-field
                                    v-model="current.delivered"
                                    label="Количество"
                                    variant="outlined"
                                    type="number"
                                    required
                                ></v-text-field>
                            </v-col>
                        </v-row>
                    </v-container>
                    <small>* обязательные поля</small>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    
                    <v-btn
                        color=""
                        text
                        @click="receiveDialog = false"
                    >
                        Отмена
                    </v-btn>

                    <v-btn
                        color="blue-darken-1"
                        text
                        @click="acceptDelivery()"
                    >
                        Принять
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Add Company Dialog -->
        <v-dialog
            v-model="addCompanyDialog"
            persistent
        >
            <v-card>
                <v-card-title>
                    <span class="text-h5">Добавить компанию</span>
                </v-card-title>

                <v-card-text>
                    <v-container>
                        <v-row>
                            <v-col
                                cols="12"
                            >
                                <v-text-field
                                    v-model="newCompany"
                                    label="Название компании *"
                                    required
                                ></v-text-field>
                            </v-col>
                        </v-row>
                    </v-container>
                    <small>* обязательные поля</small>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    
                    <v-btn
                        color="default"
                        text
                        @click="addCompanyDialog = false"
                    >
                        Отмена
                    </v-btn>

                    <v-btn
                        color="success"
                        text
                        @click="addCompany()"
                    >
                        Добавить
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Accept Product Dialog -->
        <v-dialog
            v-model="acceptProductDialog"
            persistent
        >
            <v-card
                class="min-w-5xl w-7xl" style="width: 500px"
            >
                <v-card-title>
                    <span class="text-h5">Приемка товара</span>
                </v-card-title>

                <v-card-text>
                    <v-container>
                        <v-row>
                            <v-col cols="12">{{ priemka.name }}</v-col>
                        </v-row>
                        <v-row>
                            <v-col
                                cols="12"
                            >
                                <v-textarea
                                    class="mt-2"
                                    v-model="priemka.notes"
                                    label="Примечание"
                                    variant="underlined"
                                    required
                                    density="comfortable"
                                ></v-textarea>

                                <v-text-field
                                    class="mt-2"
                                    v-model="priemka.quantity"
                                    label="Количество"
                                    variant="underlined"
                                    required
                                    density="comfortable"
                                    type="number"
                                    @keyup.enter="acceptProduct()"
                                ></v-text-field>

                                <span class="mt-2">Ед. изм.: {{ priemka.unit }}</span>
                            </v-col>
                        </v-row>
                    </v-container>
                    <!-- <small>* обязательные поля</small> -->
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    
                    <v-btn
                        color="default"
                        text
                        @click="acceptProductDialog = false"
                    >
                        Отмена
                    </v-btn>

                    <v-btn
                        color="success"
                        text
                        @click="acceptProduct()"
                    >
                        Принять
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        
    </div>
</template>


<script>
import { onMounted, reactive, ref } from "vue";
import useConstructions from "../../composables/constructions"
import useApplications from "../../composables/applications"
import Multiselect from 'vue-multiselect'
import axios from 'axios'
import ApplicationSidebar from "./ApplicationSidebar.vue";

export default {
    components: {
        Multiselect,
        ApplicationSidebar,
    },

    inject: ['isLoading'],

    data() {
        return {
            snackbar: {
                status: false,
                text: '',
                timeout: 2000,
            },
            current: {
                product: null,
                category: null,
                quantity: null,
                notes: null,
                isAddNotes: false,
                delivered: null,
            },
            options: [],
            categories: [],
            products: [],
            companies: [],
            form: {
                // 'issued_at': new Date(),
                'construction': null,
                'is_urgent': false,
                'kind': 'acquisition_of_inventory',
            },
            constructions: [],
            application: null,
            currentUser: null,

            declinedApplicationStatus: null,
            declineDialog: false,
            declinedReason: '',

            addCompanyDialog: false,
            newCompany: null,

            showPanels: ['sign'],

            receiveDialog: false,
            receiveProduct: null,

            incoming: [],
            declineProductDialog: false,
            declinedProductReason: null,
            declineInventoryApplicationId: null,

            priemka: {
                applicationProductId: null,
                name: null,
                unit: null,
                quantity: null,
                notes: null,
            },
            acceptProductDialog: false,
        }
    },

    mounted() {
        this.isLoading = true

        // get current user
        this.getCurrentUser()

        // get products -> async autocomplete TODO!
        axios.get('/api/v1/products').then((response) => {
            response.data.data.forEach((item) => {
                this.options.push(item)
            })
            
            // this.isLoading = false
        })

        // get categories
        axios.get('/api/v1/categories').then((response) => {
            response.data.data.forEach((item) => {
                this.categories.push(item)
            })
            
            // this.isLoading = false
        })

        // get companies
        this.getCompanies();

        // get constructions
        axios.get('/api/v1/constructions').then((response) => {
            this.constructions = response.data.data
        })
    },

    methods: {
        showNotes() {
            this.current.isAddNotes = true
        },

        getCurrentUser() {
            axios.get('/api/v1/me').then((response) => {
                this.currentUser = response.data

                if (this.currentUser.roles[0].title == 'Supplier' || this.currentUser.roles[0].title == 'Warehouse Manager') {
                    this.showPanels = []
                }

                // get application
                this.getApplication()
                
            })
        },

        getApplication() {
            axios.get('/api/v1/applications/' + this.$route.params.id).then((response) => {
                this.application = response.data.data

                this.products = this.application.application_application_products
                // console.log(this.products);

                this.form.construction = this.application.construction
                this.form.kind = this.application.kind

                // if warehouse manager
                // if (this.currentUser.roles[0].title == 'Warehouse Manager') {
                //     this.getIncoming();
                // }
            })
        },  

        addProduct() {
            // console.log('add product', this.current.product.name, this.current.quantity, this.current.notes)
            if (this.current.quantity == null) {
                return;
            }

            this.products.push({
                id: this.products.length + 1,
                product: this.current.product,
                category: this.current.category,
                quantity: this.current.quantity,
                notes: this.current.notes,
            })

            this.current = {
                product: null,
                category: null,
                quantity: null,
                notes: null,
                isAddNotes: false
            }
        },

        deleteProduct(item) {
            this.products = this.products.filter(function(el) { return el.id != item.id })
        },

        updateApplication() {
            this.form.products = this.products
            this.form.construction_id = this.form.construction.id

            console.log(this.form)

            try {
                axios.put(`/api/v1/applications/${this.application.id}`, this.form).then((response) => {
                    this.snackbar.text = 'Заявка успешно обновлена.'
                    this.snackbar.status = true
                })
            } catch (e) {
                console.log(e)

                if (e.response.status === 422) {
                    // errors.value = e.response.data.errors
                }

                this.snackbar.text = 'Ошибка.'
                this.snackbar.status = true
            }
        },

        signApplication(applicationStatus) {
            var data = { 'method': 'sign' }

            axios.put('/api/v1/application-statuses/' + applicationStatus.id, data).then((response) => {
                this.getApplication()

                this.snackbar.text = 'Заявка успешно подписана.'
                this.snackbar.status = true
            })
        },

        showDecline(applicationStatus) {
            this.declineDialog = true
            this.declinedApplicationStatus = applicationStatus
        },

        declineApplication() {
            var data = { 'method': 'decline', 'declined_reason': this.declinedReason }

            axios.put('/api/v1/application-statuses/' + this.declinedApplicationStatus.id, data).then((response) => {
                this.getApplication()

                this.snackbar.text = 'Заявка отклонена.'
                this.snackbar.status = true

                this.declineDialog = false
                this.declinedApplicationStatus = null
                this.declinedReason = ''
            })
        },

        makeApplicationEditable() {
            var data = { 
                'status': 'draft', 
                'products': this.products, 
                'construction_id': this.application.construction_id,
                'kind': 'acquisition_of_inventory'
            }

            axios.put('/api/v1/applications/' + this.application.id, data).then((response) => {
                this.getApplication()

                this.snackbar.text = 'Теперь заявку можно редактировать'
                this.snackbar.status = true
            })
        },

        isCanPrepareQuantity() {
            return this.currentUser != null && this.currentUser.roles[0].title == 'Supplier';
        },

        isCanReceiveQuantity() {
            return this.currentUser != null && this.currentUser.roles[0].title == 'Warehouse Manager';
        },

        isPTDEngineer() {
            return this.currentUser != null && this.currentUser.roles[0].title == 'PTD Engineer';
        },        

        isSupplier() {
            return this.currentUser != null && this.currentUser.roles[0].title == 'Supplier';
        },

        isWarehouseManager() {
            return this.currentUser != null && this.currentUser.roles[0].title == 'Warehouse Manager';
        },

        isEditable() {
            return this.currentUser != null && this.currentUser.roles[0].title == 'Supplier' && this.application.status != 'in_review';
        },

        prepareQuantity(product) {
            if (!window.confirm('Вы действительно уверены?')) {
                return
            }

            axios.put('/api/v1/application-products/' + product.id + '/prepare', product).then((response) => {
                product.prepared = response.data;
                product.toBePrepared = null;

                this.snackbar.text = 'Ценностно-материальная позиция обновлена.';
                this.snackbar.status = true;
            })
        },

        showReceiveDialog(item) {
            this.receiveDialog = true
            this.receiveProduct = item
        },  

        acceptDelivery() {
            this.receiveProduct.delivered = this.current.delivered
            this.receiveProduct.mode = 'receive'

            axios.put('/api/v1/application-products/' + this.receiveProduct.id, this.receiveProduct).then((response) => {
                this.getApplication()

                this.snackbar.text = 'Ценностно-материальная позиция обновлена.'
                this.snackbar.status = true

                this.receiveDialog = false
                this.receiveProduct = null
                this.current.delivered = null
            })
        },

        addOffer(applicationProductId) {
            var data = {
                application_product_id: applicationProductId,
                name: '',
                status: 'draft',
                price: 0,
                quantity: 0,
                file: null,
                paidSum: 0,
            };

            axios.post('/api/v1/application-offers', data).then((response) => {
                console.log(response);

                for (var i = 0; i < this.products.length; i++) {
                    if (this.products[i].id == applicationProductId) {
                        console.log(this.products[i]);

                        this.products[i].offers.push(response.data.data.offer);

                        this.snackbar.text = 'Предложение от новой компании успешно добавлено.'
                        this.snackbar.status = true                      
                    }
                }
            });
        },

        updateOffer(offer) {
            try {
                axios.put(`/api/v1/application-offers/${offer.id}`, offer).then((response) => {
                    document.getElementById('offer_' + offer.id).style.visibility = 'hidden';

                    this.snackbar.text = 'Предложение от компании сохранено.'
                    this.snackbar.status = true
                })
            } catch (e) {
                console.log(e)

                if (e.response.status === 422) {
                    // errors.value = e.response.data.errors
                }

                this.snackbar.text = 'Ошибка.'
                this.snackbar.status = true
            }
        },

        deleteOffer(offerId, applicationProductId) {
            if (!window.confirm('Вы действительно хотите?')) {
                return
            }
            
            axios.delete('/api/v1/application-offers/' + offerId).then((response) => {
                for (var i = 0; i < this.products.length; i++) {
                    if (this.products[i].id == applicationProductId) {
                        this.products[i].offers = this.products[i].offers.filter(function(el) { return el.id != offerId })
                    }
                }
            })
        },

        countSum(product) {
            return product.quantity * product.price
        },

        handleFileUpload(offer, event) {
            this.file = event.target.files[0];
            
            let formData = new FormData();
            formData.append('file', this.file);
            formData.append('offer_id', offer.id);

            axios.post('/upload-file', formData, {
                'headers': { 'Content-Type': 'multipart/form-data' }
            }).then((response) => {
                offer.file = response.data.data.file;

                this.snackbar.text = 'Счет на оплату закреплен';
                this.snackbar.status = true;
            }).catch((error) => {
                console.error(error);
            })
        },

        getCompanies() {
            axios.get('/api/v1/companies').then((response) => {
                response.data.data.forEach((item) => {
                    this.companies.push(item)
                })
                
                // this.isLoading = false
            });
        },

        showAddCompanyDialog() {
            this.addCompanyDialog = true;
        },

        addCompany() {
            var data = { "name": this.newCompany };
            axios.post('/api/v1/companies', data).then((response) => {
                this.companies.push(response.data);

                this.newCompany = null;
                this.addCompanyDialog = false;
            });
        },


        // WAREHOUSE MANAGER
        // getIncoming() {
        //     axios.get('/api/v1/inventories/' + this.application.id + '/incoming').then((response) => {
        //         this.incoming = response.data.data;
        //     })
        // },

        showAcceptProduct(item) {
            this.priemka.product = item;
            this.priemka.name = item.product.name;
            this.priemka.unit = item.product.unit;

            this.acceptProductDialog = true;
        },

        acceptProduct() {
            this.priemka.product.toBePrepared = this.priemka.quantity;

            // create inventory application
            var productData = {
                product: this.priemka.product,
                notes: this.priemka.notes,
            };

            axios.put('/api/v1/application-products/' + this.priemka.product.id + '/prepare', productData).then((response) => {
                this.priemka.product.prepared = response.data.prepared;
                this.priemka.product.toBePrepared = null;

                // accept application
                var data = {
                    'mode': 'accept'
                };

                axios.put('/api/v1/inventory-applications/' + response.data.inventory.id, data).then((response) => {
                    this.getApplication();

                    this.snackbar.text = 'Приход товара принят';
                    this.snackbar.status = true

                    this.priemka = {
                        product: null,
                        name: null,
                        notes: null,
                        quantity: null,
                    };

                    this.acceptProductDialog = false;
                })
            })


            
            
        },

        showDeclineProduct(item) {
            // show decline product dialog
            this.declineProductDialog = true;
            this.declineInventoryApplicationId = item.id;

            console.log(this.declineInventoryApplicationId);
        },

        declineProduct() {            
            var data = {
                'mode': 'decline',
                'reason': this.declinedProductReason,
            };

            axios.put('/api/v1/inventory-applications/' + this.declineInventoryApplicationId, data).then((response) => {
                // this.getIncoming();

                this.declineProductDialog = false;
                this.declineInventoryApplicationId = null;
                this.declinedProductReason = null;

                this.snackbar.text = 'Приход товара отклонен';
                this.snackbar.status = true

                this.getApplication();
            })
        },

        // incoming(items) {
        //     return items.filter(el => el.status == 'waiting');
        // },

        offerHasChanged($event, offer) {
            // console.log('offer has changed: ' + offer.id);
            document.getElementById('offer_' + offer.id).style.visibility = 'visible';
        }
    },  

    watch: {
        '$route.query': {
            handler(newValue) {
                const { status } = newValue

                console.log(status);
            },
            immediate: true,
        }
    }
}
</script>


<style>
.multiselect--active {
    z-index: 1000;
}

.v-table__wrapper {
    overflow: inherit !important;
}
</style>