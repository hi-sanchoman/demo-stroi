<template>
    <div>
        <v-container>
            <!-- <v-row no-gutters>
                <v-col cols="12">
                    <h1 class="w-full text-left">Заявки</h1>
                </v-col>
            </v-row> -->

            <v-row no-gutters class="mt-10">
                <v-col cols="12" md="3" class="border px-5 py-5">
                    <ApplicationSidebar v-if="currentUser != null" :currentUser="currentUser" />
                </v-col>

                <v-col cols="12" md="9" class="pl-0 pl-md-5 mt-4 mt-md-0">
                    <div v-if="errors">
                        <div v-for="(v, k) in errors" :key="k"
                            class="bg-red-500 text-white rounded font-bold mb-4 shadow-lg py-2 px-4 pr-0">
                            <p v-for="error in v" :key="error" class="text-sm">
                                {{ error }}
                            </p>
                        </div>
                    </div>

                    <v-form class="space-y-6">
                        <v-row v-if="
                            application != null &&
                            application.status != 'draft'
                        " class="mb-5">
                            <v-col cols="12">
                                <strong>Заявка №{{ application.id }}</strong><br />
                                Дата заявки: {{ application.issued_at }}
                            </v-col>
                        </v-row>

                        <multiselect v-model="form.construction" :options="constructions"
                            placeholder="Укажите строительный объект" :disabled="
                                application != null &&
                                application.status != 'draft'
                            " label="name" track-by="name">
                        </multiselect>

                        <v-row v-if="
                            application != null &&
                            application.status == 'draft'
                        " class="mt-5">
                            <v-col v-if="application.kind == 'product'" cols="12">Добавить товар к заявке:</v-col>
                            <v-col v-else-if="application.kind == 'equipment'" cols="12">Добавить спец. технику к
                                заявке:
                            </v-col>
                            <v-col v-else-if="application.kind == 'service'" cols="12">Добавить услугу к заявке:</v-col>
                        </v-row>

                        <v-row v-if="
                            application != null &&
                            application.status == 'draft'
                        ">
                            <template v-if="application.kind == 'product'">
                                <v-col cols="12" md="3">
                                    <multiselect v-model="current.category" :options="categories"
                                        placeholder="Укажите категорию" label="name" track-by="name"></multiselect>
                                </v-col>

                                <v-col cols="12" md="3">
                                    <multiselect v-model="current.product" :options="options"
                                        placeholder="Укажите товар" label="name" track-by="name"></multiselect>
                                </v-col>

                                <v-col cols="12" md="2">
                                    <multiselect v-model="current.unit" :options="units" placeholder="Ед. изм."
                                        label="name" track-by="name"></multiselect>
                                </v-col>
                            </template>

                            <!-- Equipment -->
                            <template v-else-if="application.kind == 'equipment'">
                                <v-col cols="12" md="4">
                                    <multiselect v-model="current.equipment" :options="equipmentOptions"
                                        placeholder="Укажите спец. технику" label="name" track-by="name"></multiselect>
                                </v-col>

                                <!-- <v-col cols="12" md="2">
                                    <v-text-field v-model="current.days" label="Срок (дней)" variant="underlined"
                                        required density="comfortable" type="number"></v-text-field>
                                </v-col> -->

                                <v-col cols="12" md="2">
                                    <multiselect v-model="current.unit" :options="units" placeholder="Ед. изм."
                                        label="name" track-by="name"></multiselect>
                                </v-col>
                            </template>

                            <!-- Service -->
                            <template v-else-if="application.kind == 'service'">
                                <!-- <v-col cols="12" md="3">
                                    <v-text-field v-model="current.category" label="Напишите категорию"
                                        variant="underlined" required density="comfortable" type="text"></v-text-field>
                                </v-col> -->
                                <v-col cols="12" md="3">
                                    <multiselect v-model="current.category" :options="categories"
                                        placeholder="Укажите категорию" label="name" track-by="name"></multiselect>
                                </v-col>

                                <v-col cols="12" md="3">
                                    <v-text-field v-model="current.service" label="Напишите название"
                                        variant="underlined" required density="comfortable" type="text"></v-text-field>
                                </v-col>

                                <!-- <v-col cols="12" md="2">
                                    <v-text-field v-model="current.unit" label="Ед. изм." variant="underlined" required
                                        density="comfortable" type="text"></v-text-field>
                                </v-col> -->
                                <v-col cols="12" md="2">
                                    <multiselect v-model="current.unit" :options="units" placeholder="Ед. изм."
                                        label="name" track-by="name"></multiselect>
                                </v-col>
                            </template>

                            <!-- Common -->
                            <v-col cols="12" md="1">
                                <v-text-field v-model="current.quantity" label="Количество" @keyup.enter="addProduct()"
                                    variant="underlined" required density="comfortable" type="number"></v-text-field>
                            </v-col>

                            <v-col cols="12" md="2">
                                <v-textarea v-if="current.isAddNotes" outlined v-model="current.notes"
                                    label="Примечание" variant="underlined" density="comfortable"
                                    @keyup.enter="addProduct()"></v-textarea>
                                <v-btn v-if="!current.isAddNotes" color="" size="small" @click="showNotes()">
                                    + примечание
                                </v-btn>
                            </v-col>

                            <v-col cols="12" md="1">
                                <v-btn size="small" color="primary" @click="addProduct()">
                                    <v-icon>mdi-plus</v-icon>
                                </v-btn>
                            </v-col>
                        </v-row>

                        <v-table style="" v-if="
                            application != null &&
                            application.kind == 'product'
                        ">
                            <thead>
                                <tr>
                                    <th>№</th>
                                    <th>Статья расходов</th>
                                    <th>Наименование ресурсов</th>
                                    <th>Ед. изм.</th>

                                    <th v-if="!isWarehouseManager() && !isSupplier()">Кол-во</th>
                                    <template v-else>
                                        <th>заказано</th>
                                        <th>фактически</th>
                                        <th>остаток</th>
                                    </template>

                                    <th>Примечание</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>
                                <template v-for="(item, index) in products" :key="item.id">
                                    <tr>
                                        <td>{{ index + 1 }}</td>
                                        <td>{{ item.category.name }}</td>
                                        <td>{{ item.product.name }}</td>
                                        <td>{{ item.unit.name }}</td>

                                        <td v-if="!isWarehouseManager() && !isSupplier()" :class="
                                            application.status == 'draft'
                                                ? 'd-flex mt-3'
                                                : ''
                                        ">
                                            <v-text-field v-model="
                                                products[index].quantity
                                            " type="number" variant="plain" density="compact" v-if="
    application.status ==
    'draft'
">
                                            </v-text-field>

                                            <!-- <span v-if="!isPTDEngineer()">
                                                {{ products[index].prepared }} /
                                            </span> -->

                                            <span v-if="
                                                application.status !=
                                                'draft'
                                            ">
                                                {{ products[index].quantity }}
                                            </span>
                                        </td>

                                        <template v-else>
                                            <td>
                                                {{ products[index].quantity }}
                                            </td>
                                            <td>
                                                {{ products[index].prepared }}
                                            </td>
                                            <td>
                                                {{
                                                        products[index].quantity -
                                                        products[index].prepared
                                                }}
                                            </td>
                                        </template>

                                        <td>{{ item.notes }}</td>

                                        <td>
                                            <v-btn v-if="
                                                application.status ==
                                                'draft'
                                            " size="small" color="error" @click="deleteProduct(item)">
                                                <v-icon>mdi-close</v-icon>
                                            </v-btn>

                                            <v-btn v-if="
                                                application.status ==
                                                'in_progress' &&
                                                isSupplier()
                                            " @click="addOffer(item.id)" color="info" size="small">
                                                + компания
                                            </v-btn>

                                            <v-btn v-if="
                                                isWarehouseManager() &&
                                                products[index].quantity !=
                                                products[index].prepared
                                            " @click="showAcceptProduct(item)" color="info" size="small">
                                                принять товар
                                            </v-btn>
                                        </td>
                                    </tr>

                                    <tr v-if="
                                        !isWarehouseManager() &&
                                        item.offers != null &&
                                        item.offers.length > 0 &&
                                        (application.status ==
                                            'in_progress' ||
                                            application.status ==
                                            'in_review')
                                    " class="bg-slate-100">
                                        <td colspan="9" class="border-none">
                                            <v-table class="mt-4 mb-8 mx-8 border" style="overflow: visible">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 25%">
                                                            Название компании
                                                        </th>
                                                        <th style="width: 10%">
                                                            Кол-во
                                                        </th>
                                                        <th style="width: 10%">
                                                            Цена за ед.
                                                        </th>
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
                                            <multiselect v-if="
                                                offer.status ==
                                                'draft' &&
                                                isEditable()
                                            " v-model="
    offer.company
" :options="
    companies
" placeholder="Укажите компанию" label="name" track-by="id">
                                            </multiselect>

                                            <v-btn v-if="
                                                offer.status ==
                                                'draft' &&
                                                isEditable()
                                            " class="mt-1" color="primary" size="x-small" plain @click="
    showAddCompanyDialog()
">
                                                Добавить
                                                компанию
                                            </v-btn>
                                        </div>

                                        <span v-if="
                                            !isEditable() &&
                                            offer !=
                                            null &&
                                            offer.company !=
                                            null
                                        ">{{
        offer
            .company
            .name
}}</span>
                                    </td>
                                    <td>
                                        <v-text-field v-if="
                                            offer.status ==
                                            'draft' &&
                                            isEditable()
                                        " @change="
    offerHasChanged(
        $event,
        offer
    )
" v-model="
    offer.quantity
" label="" type="number" variant="underlined" required>
                                        </v-text-field>
                                        <span v-if="
                                            !isEditable()
                                        ">{{
        offer.quantity
}}</span>
                                    </td>
                                    <td>
                                        <v-text-field v-if="
                                            offer.status ==
                                            'draft' &&
                                            isEditable()
                                        " v-model="
    offer.price
" label="" type="number" variant="underlined" required>
                                        </v-text-field>
                                        <span v-if="
                                            !isEditable()
                                        ">{{
        offer.price
}}
                                            тг</span>
                                    </td>
                                    <td>
                                        {{
                                                offer.price *
                                                offer.quantity
                                        }}
                                        тг
                                    </td>

                                    <td>
                                        <span v-if="
                                            offer.file !=
                                            null
                                        ">
                                            <a class="px-2 py-2 mr-2 border text-black text-decoration-none hover:bg-slate-100 cursor-pointer"
                                                target="_blank" :href="
                                                    '/uploads/' +
                                                    offer.file
                                                ">Просмотр</a>
                                        </span>

                                        <input v-if="
                                            isEditable()
                                        " type="file" id="file" v-on:change="
    handleFileUpload(
        offer,
        $event
    )
" />
                                    </td>

                                    <td>
                                        <v-btn v-if="
                                            offer.status ==
                                            'draft' &&
                                            isEditable()
                                        " :id="
    'offer_' +
    offer.id
" @click="
    updateOffer(
        offer
    )
" color="success" size="small" class="mr-2">
                                            Сохранить
                                        </v-btn>

                                        <v-btn @click="
                                            deleteOffer(
                                                offer.id,
                                                item.id
                                            )
                                        " color="error" size="small" v-if="
    offer.status ==
    'draft' &&
    isEditable()
">
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

<v-table style="" v-else-if="application != null && application.kind == 'service'">
    <thead>
        <tr>
            <th>№</th>
            <th>Статья расходов</th>
            <th>Наименование ресурсов</th>
            <th>Ед. изм.</th>

            <th v-if="!isWarehouseManager() && !isSupplier()">Кол-во</th>
            <template v-else>
                <th>заказано</th>
                <th>фактически</th>
                <th>остаток</th>
            </template>

            <th>Примечание</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <template v-for="(item, index) in services" :key="item.id">
            <tr>
                <td>{{ index + 1 }}</td>
                <td>{{ item.category }}</td>
                <td>{{ item.service }}</td>
                <td>{{ item.unit }}</td>

                <td v-if="!isWarehouseManager() && !isSupplier()" :class="
                    application.status == 'draft'
                        ? 'd-flex mt-3'
                        : ''
                ">
                    <v-text-field v-model="
                        services[index].quantity
                    " type="number" variant="plain" density="compact" v-if="
    application.status ==
    'draft'
">
                    </v-text-field>

                    <!-- <span v-if="!isPTDEngineer()">
                        {{ services[index].prepared }} /
                    </span> -->
                    <span v-if="
                        application.status !=
                        'draft'
                    ">
                        {{ services[index].quantity }}
                    </span>
                </td>

                <template v-else>
                    <td>
                        {{ services[index].quantity }}
                    </td>
                    <td>
                        {{ services[index].prepared }}
                    </td>
                    <td>
                        {{
                                services[index].quantity -
                                services[index].prepared
                        }}
                    </td>
                </template>

                <td>{{ item.notes }}</td>

                <td>
                    <v-btn v-if="
                        application.status ==
                        'draft'
                    " size="small" color="error" @click="deleteProduct(item)">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>

                    <v-btn v-if="
                        application.status ==
                        'in_progress' &&
                        isSupplier()
                    " @click="addOffer(item.id)" color="info" size="small">
                        + компания
                    </v-btn>

                    <v-btn v-if="
                        isWarehouseManager() &&
                        services[index].quantity !=
                        services[index].prepared
                    " @click="showAcceptProduct(item)" color="info" size="small">
                        принять товар
                    </v-btn>
                </td>
            </tr>

            <tr v-if="
                !isWarehouseManager() &&
                item.offers != null &&
                item.offers.length > 0 &&
                (application.status ==
                    'in_progress' ||
                    application.status ==
                    'in_review')
            " class="bg-slate-100">
                <td colspan="9" class="border-none">
                    <v-table class="mt-4 mb-8 mx-8 border" style="overflow: visible">
                        <thead>
                            <tr>
                                <th style="width: 25%">
                                    Название компании
                                </th>
                                <th style="width: 10%">
                                    Кол-во
                                </th>
                                <th style="width: 10%">
                                    Цена за ед.
                                </th>
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
                    <multiselect v-if="
                        offer.status ==
                        'draft' &&
                        isEditable()
                    " v-model="
    offer.company
" :options="
    companies
" placeholder="Укажите компанию" label="name" track-by="id">
                    </multiselect>

                    <v-btn v-if="
                        offer.status ==
                        'draft' &&
                        isEditable()
                    " class="mt-1" color="primary" size="x-small" plain @click="
    showAddCompanyDialog()
">
                        Добавить
                        компанию
                    </v-btn>
                </div>

                <span v-if="
                    !isEditable() &&
                    offer !=
                    null &&
                    offer.company !=
                    null
                ">{{
        offer
            .company
            .name
}}</span>
            </td>
            <td>
                <v-text-field v-if="
                    offer.status ==
                    'draft' &&
                    isEditable()
                " @change="
    offerHasChanged(
        $event,
        offer
    )
" v-model="
    offer.quantity
" label="" type="number" variant="underlined" required>
                </v-text-field>
                <span v-if="
                    !isEditable()
                ">{{
        offer.quantity
}}</span>
            </td>
            <td>
                <v-text-field v-if="
                    offer.status ==
                    'draft' &&
                    isEditable()
                " v-model="
    offer.price
" label="" type="number" variant="underlined" required>
                </v-text-field>
                <span v-if="
                    !isEditable()
                ">{{
        offer.price
}}
                    тг</span>
            </td>
            <td>
                {{
                        offer.price *
                        offer.quantity
                }}
                тг
            </td>

            <td>
                <span v-if="
                    offer.file !=
                    null
                ">
                    <a class="px-2 py-2 mr-2 border text-black text-decoration-none hover:bg-slate-100 cursor-pointer"
                        target="_blank" :href="
                            '/uploads/' +
                            offer.file
                        ">Просмотр</a>
                </span>

                <input v-if="
                    isEditable()
                " type="file" id="file" v-on:change="
    handleFileUpload(
        offer,
        $event
    )
" />
            </td>

            <td>
                <v-btn v-if="
                    offer.status ==
                    'draft' &&
                    isEditable()
                " :id="
    'offer_' +
    offer.id
" @click="
    updateOffer(
        offer
    )
" color="success" size="small" class="mr-2">
                    Сохранить
                </v-btn>

                <v-btn @click="
                    deleteOffer(
                        offer.id,
                        item.id
                    )
                " color="error" size="small" v-if="
    offer.status ==
    'draft' &&
    isEditable()
">
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

<v-table style="" v-else-if="
    application != null &&
    application.kind == 'equipment'
">
    <thead>
        <tr>
            <th>№</th>
            <th>Наименование спец. техники</th>
            <th>Ед. изм.</th>

            <th v-if="!isWarehouseManager() && !isSupplier()">Кол-во</th>
            <template v-else>
                <th>заказано</th>
                <th>фактически</th>
                <th>остаток</th>
            </template>

            <th>Примечание</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <template v-for="(item, index) in equipments" :key="item.id">
            <tr>
                <td>{{ item.id }}</td>
                <td>{{ item.equipment.name }}</td>
                <td>{{ item.unit.name }}</td>
                <td v-if="!isWarehouseManager() && !isSupplier()" :class="
                    application.status == 'draft'
                        ? 'd-flex mt-3'
                        : ''
                ">
                    <v-text-field v-model="
                        equipments[index].quantity
                    " type="number" variant="plain" density="compact" v-if="
    application.status ==
    'draft'
">
                    </v-text-field>

                    <!-- <span v-if="!isPTDEngineer()">
                        {{ equipments[index].prepared }} /
                    </span> -->
                    <span v-if="
                        application.status !=
                        'draft'
                    ">
                        {{ equipments[index].quantity }}
                    </span>
                </td>

                <template v-else>
                    <td>
                        {{ equipments[index].quantity }}
                    </td>
                    <td>
                        {{ equipments[index].prepared }}
                    </td>
                    <td>
                        {{
                                equipments[index].quantity -
                                equipments[index].prepared
                        }}
                    </td>
                </template>

                <td>{{ item.notes }}</td>

                <td>
                    <v-btn v-if="
                        application.status ==
                        'in_progress' &&
                        item.status == 'completed' &&
                        isSupplier()
                    " @click="addOffer(item.id)" color="info" size="small">
                        + компания
                    </v-btn>

                    <v-btn v-if="
                        application.status ==
                        'in_progress' &&
                        item.status == 'completed' &&
                        isSupplier()
                    " @click="showEquipmentNotes(item)" class="ml-2" color="warning" size="small">
                        дневник
                    </v-btn>

                    <v-btn v-if="
                        isWarehouseManager() &&
                        application.status == 'in_progress' &&
                        equipments[index].quantity !=
                        equipments[index].prepared
                    " @click="showAcceptProduct(item)" color="info" size="small">
                        принять технику
                    </v-btn>

                    <v-btn v-if="application != null && application.status == 'draft' && isPTDEngineer()" size="small"
                        color="error" @click="deleteProduct(item)">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </td>
            </tr>
            <tr v-if="
                !isWarehouseManager() &&
                item.offers != null &&
                item.offers.length > 0 &&
            
                (application.status ==
                    'in_progress' ||
                    application.status ==
                    'in_review')
            " class="bg-slate-100">
                <td colspan="9" class="border-none">
                    <v-table class="mt-4 mb-8 mx-8 border" style="overflow: visible">
                        <thead>
                            <tr>
                                <th style="width: 25%">
                                    Название компании
                                </th>
                                <th style="width: 10%">
                                    Кол-во
                                </th>
                                <th style="width: 10%">
                                    Цена за ед.
                                </th>
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
                    <multiselect v-if="
                        offer.status ==
                        'draft' &&
                        isEditable()
                    " v-model="
    offer.company
" :options="
    companies
" placeholder="Укажите компанию" label="name" track-by="id">
                    </multiselect>

                    <v-btn v-if="
                        offer.status ==
                        'draft' &&
                        isEditable()
                    " class="mt-1" color="primary" size="x-small" plain @click="
    showAddCompanyDialog()
">
                        Добавить
                        компанию
                    </v-btn>
                </div>

                <span v-if="
                    !isEditable() &&
                    offer !=
                    null &&
                    offer.company !=
                    null
                ">{{
        offer
            .company
            .name
}}</span>
            </td>
            <td>
                <v-text-field v-if="
                    offer.status ==
                    'draft' &&
                    isEditable()
                " @change="
    offerHasChanged(
        $event,
        offer
    )
" v-model="
    offer.quantity
" label="" type="number" variant="underlined" required>
                </v-text-field>
                <span v-if="
                    !isEditable()
                ">{{
        offer.quantity
}}</span>
            </td>
            <td>
                <v-text-field v-if="
                    offer.status ==
                    'draft' &&
                    isEditable()
                " v-model="
    offer.price
" label="" type="number" variant="underlined" required>
                </v-text-field>
                <span v-if="
                    !isEditable()
                ">{{
        offer.price
}}
                    тг</span>
            </td>
            <td>
                {{
                        offer.price *
                        offer.quantity
                }}
                тг
            </td>

            <td>
                <span v-if="
                    offer.file !=
                    null
                ">
                    <a class="px-2 py-2 mr-2 border text-black text-decoration-none hover:bg-slate-100 cursor-pointer"
                        target="_blank" :href="
                            '/uploads/' +
                            offer.file
                        ">Просмотр</a>
                </span>

                <input v-if="
                    isEditable()
                " type="file" id="file" v-on:change="
    handleFileUpload(
        offer,
        $event
    )
" />
            </td>

            <td>
                <v-btn v-if="
                    offer.status ==
                    'draft' &&
                    isEditable()
                " :id="
    'offer_' +
    offer.id
" @click="
    updateOffer(
        offer
    )
" color="success" size="small" class="mr-2">
                    Сохранить
                </v-btn>

                <v-btn @click="
                    deleteOffer(
                        offer.id,
                        item.id
                    )
                " color="error" size="small" v-if="
    offer.status ==
    'draft' &&
    isEditable()
">
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

<v-btn v-if="
    form.construction != null &&
    (products.length > 0 ||
        equipments.length > 0 ||
        services.length > 0) &&
    isPTDEngineer() &&
    application.status == 'draft'
" class="mt-5" @click="updateApplication" color="primary">
    Сохранить
</v-btn>
</v-form>

<v-container class="mt-5 px-0 md:px-2" v-if="application != null">
    <v-row v-if="
        currentUser != null &&
        application.status == 'declined' &&
        application.owner_id == currentUser.id
    " class="mb-5" no-gutters>
        <v-col cols="12">
            <v-btn color="primary" @click="makeApplicationEditable()" class="">
                Внести корректировки в заявку
            </v-btn>
        </v-col>
    </v-row>

    <!-- For supplier -->
    <v-row v-if="
        currentUser != null &&
        application.status != 'in_progress' &&
        isSupplier()
    " class="mb-5" no-gutters>
        <v-col cols="12">
            <v-btn color="primary" @click="makeApplicationEditableBySupplier()" class="">
                Дополнить заявку
            </v-btn>
        </v-col>
    </v-row>

    <v-row no-gutters>
        <v-col no-gutters cols="12">
            <v-expansion-panels no-gutters v-model="showPanels">
                <v-expansion-panel value="sign">
                    <v-expansion-panel-title>
                        Подписание заявки
                    </v-expansion-panel-title>

                    <v-expansion-panel-text no-gutters style="" class="px-0">
                        <v-list-item no-gutters v-for="(
                                                    item, index
                                                ) in application.application_application_statuses" :key="item.id">
                            <v-list-item-header>
                                <v-list-item-title>
                                    <v-col cols="12" class="px-0">
                                        <strong>{{
                                                index + 1
                                        }}.
                                            {{
                                                    item
                                                        .application_path
                                                        .position
                                            }}</strong>
                                        -
                                        {{
                                                item
                                                    .application_path
                                                    .responsible
                                                    .name
                                        }}
                                    </v-col>

                                    <!-- declined -->
                                    <span v-if="
                                        item.status ==
                                        'declined'
                                    ">
                                        <v-chip class="mr-2" color="red" text-color="white">
                                            Отклонено
                                        </v-chip>

                                        <span>{{
                                                item.declined_reason
                                        }}</span>
                                        -
                                        <span>{{
                                                item.updated_at
                                        }}</span>
                                    </span>
                                    <!-- accepted -->
                                    <span v-else-if="
                                        item.status ==
                                        'accepted'
                                    ">
                                        <v-chip class="mr-2" color="green" text-color="white">
                                            Одобрено
                                        </v-chip>

                                        <span>{{
                                                item.updated_at
                                        }}</span>
                                    </span>

                                    <!-- incoming -->
                                    <span v-else-if="
                                        currentUser !=
                                        null &&
                                        item.status ==
                                        'incoming' &&
                                        item
                                            .application_path
                                            .responsible
                                            .id ==
                                        currentUser.id
                                    ">
                                        <v-btn size="small" color="success" @click="
                                            signApplication(
                                                item
                                            )
                                        " class="mr-5">
                                            Подписать
                                        </v-btn>

                                        <v-btn v-if="
                                            item
                                                .application_path
                                                .responsible
                                                .id !=
                                            application.owner_id
                                        " size="small" color="error" @click="
    showDecline(
        item
    )
" class="">
                                            Отклонить
                                        </v-btn>
                                    </span>
                                    <span v-else>...</span>
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
<v-snackbar v-model="snackbar.status" :timeout="snackbar.timeout">
    {{ snackbar.text }}

    <template v-slot:actions>
        <v-btn color="blue" variant="text" @click="snackbar.status = false">
            Закрыть
        </v-btn>
    </template>
</v-snackbar>

<!-- Decline dialog -->
<v-dialog v-model="declineDialog" persistent>
    <v-card>
        <v-card-title>
            <span class="text-h5">Отклонить заявку?</span>
        </v-card-title>

        <v-card-text>
            <v-container>
                <v-row>
                    <v-col cols="12">
                        <v-textarea v-model="declinedReason" label="Причина отклонения*" required></v-textarea>
                    </v-col>
                </v-row>
            </v-container>
            <small>* обязательные поля</small>
        </v-card-text>
        <v-card-actions>
            <v-spacer></v-spacer>

            <v-btn color="blue-darken-1" text @click="declineDialog = false">
                Отмена
            </v-btn>

            <v-btn color="red-darken-1" text @click="declineApplication()">
                Отклонить
            </v-btn>
        </v-card-actions>
    </v-card>
</v-dialog>

<!-- Decline Product dialog -->
<v-dialog v-model="declineProductDialog" persistent>
    <v-card>
        <v-card-title>
            <span class="text-h5">Отклонить приход?</span>
        </v-card-title>

        <v-card-text>
            <v-container>
                <v-row>
                    <v-col cols="12">
                        <v-textarea v-model="declinedProductReason" label="Причина отклонения*" required></v-textarea>
                    </v-col>
                </v-row>
            </v-container>
            <small>* обязательные поля</small>
        </v-card-text>
        <v-card-actions>
            <v-spacer></v-spacer>

            <v-btn color="blue-darken-1" text @click="declineProductDialog = false">
                Отмена
            </v-btn>

            <v-btn color="red-darken-1" text @click="declineProduct()">
                Отклонить
            </v-btn>
        </v-card-actions>
    </v-card>
</v-dialog>

<v-dialog v-model="receiveDialog" persistent>
    <v-card>
        <v-card-title>
            <span class="text-h5">Получение материально-товарных ценностей</span>
        </v-card-title>

        <v-card-text>
            <v-container>
                <v-row>
                    <v-col cols="12">
                        <v-text-field v-model="current.delivered" label="Количество" variant="outlined" type="number"
                            required></v-text-field>
                    </v-col>
                </v-row>
            </v-container>
            <small>* обязательные поля</small>
        </v-card-text>
        <v-card-actions>
            <v-spacer></v-spacer>

            <v-btn color="" text @click="receiveDialog = false">
                Отмена
            </v-btn>

            <v-btn color="blue-darken-1" text @click="acceptDelivery()">
                Принять
            </v-btn>
        </v-card-actions>
    </v-card>
</v-dialog>

<!-- Add Company Dialog -->
<v-dialog v-model="addCompanyDialog" persistent>
    <v-card>
        <v-card-title>
            <span class="text-h5">Добавить компанию</span>
        </v-card-title>

        <v-card-text>
            <v-container>
                <v-row>
                    <v-col cols="12">
                        <v-text-field v-model="newCompany" label="Название компании *" required></v-text-field>
                    </v-col>
                </v-row>
            </v-container>
            <small>* обязательные поля</small>
        </v-card-text>
        <v-card-actions>
            <v-spacer></v-spacer>

            <v-btn color="default" text @click="addCompanyDialog = false">
                Отмена
            </v-btn>

            <v-btn color="success" text @click="addCompany()">
                Добавить
            </v-btn>
        </v-card-actions>
    </v-card>
</v-dialog>

<!-- Accept Product Dialog -->
<v-dialog v-model="acceptProductDialog" persistent>
    <v-card class="oks-dialog min-w-5xl w-7xl" style="">
        <v-card-title>
            <span class="text-h5">Приемка товара/техники/услуги</span>
        </v-card-title>

        <v-card-text>
            <v-container>
                <v-row>
                    <v-col cols="12">{{ priemka.name }}</v-col>
                </v-row>
                <v-row>
                    <v-col cols="12">
                        <v-text-field class="mt-2" v-model="priemka.quantity" label="Количество" variant="underlined"
                            required density="comfortable" type="number" @keyup.enter="acceptProduct()"></v-text-field>

                        <v-textarea class="mt-2" v-model="priemka.notes" label="Примечание" variant="underlined"
                            required density="comfortable"></v-textarea>

                        <span class="mt-2">Ед. изм.: {{ priemka.unit }}</span>
                    </v-col>
                </v-row>
            </v-container>
            <!-- <small>* обязательные поля</small> -->
        </v-card-text>
        <v-card-actions>
            <v-spacer></v-spacer>

            <v-btn color="default" text @click="acceptProductDialog = false">
                Отмена
            </v-btn>

            <v-btn color="success" text @click="acceptProduct()">
                Принять
            </v-btn>
        </v-card-actions>
    </v-card>
</v-dialog>



<!-- equipment notes dialog -->
<v-dialog v-model="equipmentHistoryDialog">
    <v-card>
        <v-card-title>
            <span class="text-h5">История спец. техники</span>
        </v-card-title>

        <v-card-text>
            <v-container>
                <v-row no-gutters class="">
                    Полных рабочих дней: {{ getWorkingDays() }}
                    <v-col cols="12" class="">
                        <v-table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Дата</th>
                                    <th>Часов отработано</th>
                                    <th>Примечание</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(note, n) in equipmentNotes" :key="note.id">
                                    <td>{{ n + 1 }}</td>
                                    <td>{{ note.created_at }}</td>
                                    <td>{{ note.hours }}</td>
                                    <td>{{ note.notes }}</td>
                                    <td>
                                        <!-- <v-btn color="error" size="small">удалить</v-btn> -->
                                    </td>
                                </tr>
                            </tbody>
                        </v-table>
                    </v-col>
                </v-row>
            </v-container>
        </v-card-text>
    </v-card>
</v-dialog>
</div>
</template>

<script>
// import { onMounted, reactive, ref } from "vue";
// import useConstructions from "../../composables/constructions"
// import useApplications from "../../composables/applications"
import Multiselect from "vue-multiselect";
import axios from "axios";
import ApplicationSidebar from "./ApplicationSidebar.vue";
import { store } from "../../store";

export default {
    components: {
        Multiselect,
        ApplicationSidebar,
    },

    inject: ["isLoading"],

    data() {
        return {
            store,

            snackbar: {
                status: false,
                text: "",
                timeout: 2000,
            },
            current: {
                product: null,
                equipment: null,
                service: null,
                category: null,
                unit: null,
                quantity: null,
                notes: null,
                isAddNotes: false,
                delivered: null,
                days: null,
            },
            options: [],
            categories: [],
            equipmentOptions: [],
            units: [],
            companies: [],

            products: [],
            services: [],
            equipments: [],

            form: {
                // 'issued_at': new Date(),
                construction: null,
                is_urgent: false,
                kind: null,
                products: [],
                equipments: [],
                services: [],
            },

            constructions: [],
            application: null,
            currentUser: null,

            declinedApplicationStatus: null,
            declineDialog: false,
            declinedReason: "",

            addCompanyDialog: false,
            newCompany: null,

            showPanels: ["sign"],

            receiveDialog: false,
            receiveProduct: null,

            incoming: [],
            declineProductDialog: false,
            declinedProductReason: null,
            declineInventoryApplicationId: null,

            priemka: {
                applicationProductId: null,
                applicationEquipmentId: null,
                product: null,
                equipment: null,
                service: null,
                name: null,
                unit: null,
                quantity: null,
                notes: null,
            },
            acceptProductDialog: false,

            selectedEquipment: null,
            equipmentNotes: [],
            equipmentHistoryDialog: false,
        };
    },

    mounted() {
        this.isLoading = true;

        // get current user
        this.getCurrentUser();

        // get products -> async autocomplete TODO!
        axios.get("/api/v1/products").then((response) => {
            response.data.data.forEach((item) => {
                this.options.push(item);
            });

            // this.isLoading = false
        });

        // get categories
        axios.get("/api/v1/categories").then((response) => {
            response.data.data.forEach((item) => {
                this.categories.push(item);
            });

            // this.isLoading = false
        });

        // get units
        axios.get("/api/v1/units").then((response) => {
            response.data.data.forEach((item) => {
                // console.log(item)
                this.units.push(item);
            });

            // this.isLoading = false
        });

        // get companies
        this.getCompanies();

        // get constructions
        axios.get("/api/v1/constructions").then((response) => {
            this.constructions = response.data.data;
        });

        // get equipments
        axios.get("/api/v1/equipments").then((response) => {
            this.equipmentOptions = response.data.data;
        });
    },

    methods: {
        showNotes() {
            this.current.isAddNotes = true;
        },

        getCurrentUser() {
            axios.get("/api/v1/me").then((response) => {
                this.currentUser = response.data;
                console.log(this.currentUser);

                if (
                    this.currentUser.roles[0].title == "Supplier" ||
                    this.currentUser.roles[0].title == "Warehouse Manager"
                ) {
                    this.showPanels = [];
                }

                // get application
                this.getApplication();
            });
        },

        getApplication() {
            axios
                .get("/api/v1/applications/" + this.$route.params.id)
                .then((response) => {
                    this.application = response.data.data;
                    // console.log(this.application);

                    this.products =
                        this.application.application_application_products;
                    // console.log(this.products);

                    this.equipments = this.application.application_equipments;
                    // console.log('loaded equipments', this.equipments);

                    this.services = this.application.application_services;

                    this.form.construction = this.application.construction;
                    this.form.kind = this.application.kind;

                    // count badges
                    this.getCountNewApplications();
                });
        },

        addProduct() {
            // console.log('add product', this.current.product.name, this.current.quantity, this.current.notes)
            if (this.application == null || this.current.quantity == null) {
                return;
            }

            if (this.application.kind == "product") {
                this.products.push({
                    id: this.products.length + 1,
                    product: this.current.product,
                    unit: this.current.unit,
                    category: this.current.category,
                    quantity: this.current.quantity,
                    notes: this.current.notes,
                });
            } else if (this.application.kind == "equipment") {
                this.equipments.push({
                    id: this.equipments.length + 1,
                    equipment: this.current.equipment,
                    quantity: this.current.quantity,
                    notes: this.current.notes,
                    unit: this.current.unit,
                });

                // console.log(this.equipments);
            } else if (this.application.kind == "service") {
                this.services.push({
                    id: this.services.length + 1,
                    service: this.current.service,
                    // unit: this.current.unit,
                    unit: this.current.unit.name,
                    // category: this.current.category,
                    category: this.current.category.name,
                    quantity: this.current.quantity,
                    notes: this.current.notes,
                });

            }

            this.current = {
                product: null,
                service: null,
                equipment: null,
                unit: null,
                category: null,
                quantity: null,
                notes: null,
                isAddNotes: false,
                days: null,
            };
        },

        deleteProduct(item) {
            if (this.form.kind == "product") {
                this.products = this.products.filter(function (el) {
                    return el.id != item.id;
                });
            } else if (this.form.kind == "equipment") {
                this.equipments = this.equipments.filter(
                    (el) => el.id != item.id
                );
            } else if (this.form.kind == "service") {
                this.services = this.services.filter(
                    (el) => el.id != item.id
                );
            }
        },

        updateApplication() {
            this.form.products = this.products;
            this.form.equipments = this.equipments;
            this.form.services = this.services;
            this.form.construction_id = this.form.construction.id;

            // console.log(this.form);

            try {
                axios
                    .put(
                        `/api/v1/applications/${this.application.id}`,
                        this.form
                    )
                    .then((response) => {
                        this.snackbar.text = "Заявка успешно обновлена.";
                        this.snackbar.status = true;
                    });
            } catch (e) {
                console.log(e);

                if (e.response.status === 422) {
                    // errors.value = e.response.data.errors
                }

                this.snackbar.text = "Ошибка.";
                this.snackbar.status = true;
            }
        },

        signApplication(applicationStatus) {
            var data = { method: "sign" };

            axios
                .put(
                    "/api/v1/application-statuses/" + applicationStatus.id,
                    data
                )
                .then((response) => {
                    this.getApplication();

                    this.snackbar.text = "Заявка успешно подписана.";
                    this.snackbar.status = true;
                });
        },

        showDecline(applicationStatus) {
            this.declineDialog = true;
            this.declinedApplicationStatus = applicationStatus;
        },

        declineApplication() {
            var data = {
                method: "decline",
                declined_reason: this.declinedReason,
            };

            axios
                .put(
                    "/api/v1/application-statuses/" +
                    this.declinedApplicationStatus.id,
                    data
                )
                .then((response) => {
                    this.getApplication();

                    this.snackbar.text = "Заявка отклонена.";
                    this.snackbar.status = true;

                    this.declineDialog = false;
                    this.declinedApplicationStatus = null;
                    this.declinedReason = "";
                });
        },

        makeApplicationEditable() {
            var data = {
                status: "draft",
                products: this.products,
                services: this.services,
                equipments: this.euipments,
                construction_id: this.application.construction_id,
                kind: this.application.kind,
            };

            axios
                .put("/api/v1/applications/" + this.application.id, data)
                .then((response) => {
                    this.getApplication();

                    this.snackbar.text = "Теперь заявку можно редактировать";
                    this.snackbar.status = true;
                });
        },

        makeApplicationEditableBySupplier() {
            var data = {
                status: "in_progress",
                products: this.products,
                equipments: this.equipments,
                construction_id: this.application.construction_id,
                kind: this.application.kind,
            };

            axios
                .put("/api/v1/applications/" + this.application.id, data)
                .then((response) => {
                    this.getApplication();

                    this.snackbar.text = "Теперь заявку можно дополнить";
                    this.snackbar.status = true;
                });
        },

        isCanPrepareQuantity() {
            return (
                this.currentUser != null &&
                this.currentUser.roles[0].title == "Supplier"
            );
        },

        isCanReceiveQuantity() {
            return (
                this.currentUser != null &&
                this.currentUser.roles[0].title == "Warehouse Manager"
            );
        },

        isPTDEngineer() {
            return (
                this.currentUser != null &&
                this.currentUser.roles[0].title == "PTD Engineer"
            );
        },

        isSupplier() {
            // console.log('cheking for supplier');
            return (
                this.currentUser != null &&
                (this.currentUser.roles[0].title == "Supplier" ||
                    this.currentUser.roles[0].title == "Supervisor")
            );
        },

        isWarehouseManager() {
            if (this.application.kind == 'equipment') {
                return this.currentUser != null && this.currentUser.roles[0].title == 'Section Manager';
            }

            return (
                this.currentUser != null &&
                this.currentUser.roles[0].title == "Warehouse Manager"
            );
        },

        isEditable() {
            return (
                this.currentUser != null &&
                this.isSupplier() &&
                this.application.status != "in_review"
            );
        },

        prepareQuantity(product) {
            if (!window.confirm("Вы действительно уверены?")) {
                return;
            }

            axios
                .put(
                    "/api/v1/application-products/" + product.id + "/prepare",
                    product
                )
                .then((response) => {
                    product.prepared = response.data;
                    product.toBePrepared = null;

                    this.snackbar.text =
                        "Ценностно-материальная позиция обновлена.";
                    this.snackbar.status = true;
                });
        },

        showReceiveDialog(item) {
            this.receiveDialog = true;
            this.receiveProduct = item;
        },

        acceptDelivery() {
            this.receiveProduct.delivered = this.current.delivered;
            this.receiveProduct.mode = "receive";

            axios
                .put(
                    "/api/v1/application-products/" + this.receiveProduct.id,
                    this.receiveProduct
                )
                .then((response) => {
                    this.getApplication();

                    this.snackbar.text =
                        "Ценностно-материальная позиция обновлена.";
                    this.snackbar.status = true;

                    this.receiveDialog = false;
                    this.receiveProduct = null;
                    this.current.delivered = null;
                });
        },

        addOffer(id) {
            var data = {
                application_product_id: id,
                application_equipment_id: id,
                application_service_id: id,
                name: "",
                status: "draft",
                price: 0,
                quantity: 0,
                file: null,
                paidSum: 0,
            };

            if (this.application.kind == 'product') {
                axios.post("/api/v1/application-offers", data).then((response) => {
                    // console.log(response);

                    for (var i = 0; i < this.products.length; i++) {
                        if (this.products[i].id == id) {
                            // console.log(this.products[i]);

                            this.products[i].offers.push(response.data.data.offer);

                            this.snackbar.text =
                                "Предложение от новой компании успешно добавлено.";
                            this.snackbar.status = true;
                        }
                    }
                });

                return;
            }

            if (this.application.kind == 'service') {
                axios.post("/api/v1/service-offers", data).then((response) => {
                    // console.log(response);

                    for (var i = 0; i < this.services.length; i++) {
                        if (this.services[i].id == id) {
                            // console.log(this.products[i]);

                            this.services[i].offers.push(response.data.data.offer);

                            this.snackbar.text =
                                "Предложение от новой компании успешно добавлено.";
                            this.snackbar.status = true;
                        }
                    }
                });

                return;
            }

            if (this.application.kind == 'equipment') {
                axios.post("/api/v1/equipment-offers", data).then((response) => {
                    console.log(response);

                    for (var i = 0; i < this.equipments.length; i++) {
                        if (this.equipments[i].id == id) {
                            // console.log(this.products[i]);

                            this.equipments[i].offers.push(response.data.data.offer);

                            this.snackbar.text =
                                "Предложение от новой компании успешно добавлено.";
                            this.snackbar.status = true;
                        }
                    }
                });
            }
        },

        updateOffer(offer) {
            try {
                if (this.application.kind == 'product') {
                    axios
                        .put(`/api/v1/application-offers/${offer.id}`, offer)
                        .then((response) => {
                            if (response.data.error) {
                                this.snackbar.text = response.data.error;
                                this.snackbar.status = true;

                                return;
                            }

                            document.getElementById(
                                "offer_" + offer.id
                            ).style.visibility = "hidden";

                            this.snackbar.text =
                                "Предложение от компании сохранено.";
                            this.snackbar.status = true;
                        });

                    return;
                }

                if (this.application.kind == 'service') {
                    axios
                        .put(`/api/v1/service-offers/${offer.id}`, offer)
                        .then((response) => {
                            if (response.data.error) {
                                this.snackbar.text = response.data.error;
                                this.snackbar.status = true;

                                return;
                            }

                            document.getElementById(
                                "offer_" + offer.id
                            ).style.visibility = "hidden";

                            this.snackbar.text =
                                "Предложение от компании сохранено.";
                            this.snackbar.status = true;
                        });

                    return;
                }

                if (this.application.kind == 'equipment') {
                    axios
                        .put(`/api/v1/equipment-offers/${offer.id}`, offer)
                        .then((response) => {
                            if (response.data.error) {
                                this.snackbar.text = response.data.error;
                                this.snackbar.status = true;

                                return;
                            }

                            document.getElementById(
                                "offer_" + offer.id
                            ).style.visibility = "hidden";

                            this.snackbar.text =
                                "Предложение от компании сохранено.";
                            this.snackbar.status = true;
                        });

                    return;
                }
            } catch (e) {
                console.log(e);

                if (e.response.status === 422) {
                    // errors.value = e.response.data.errors
                }

                this.snackbar.text = "Ошибка.";
                this.snackbar.status = true;
            }
        },

        deleteOffer(offerId, id) {
            if (!window.confirm("Вы действительно хотите?")) {
                return;
            }

            if (this.application.kind == 'product') {
                axios
                    .delete("/api/v1/application-offers/" + offerId)
                    .then((response) => {
                        for (var i = 0; i < this.products.length; i++) {
                            if (this.products[i].id == id) {
                                this.products[i].offers = this.products[
                                    i
                                ].offers.filter(function (el) {
                                    return el.id != offerId;
                                });
                            }
                        }
                    });
                return;
            }

            if (this.application.kind == 'service') {
                axios
                    .delete("/api/v1/service-offers/" + offerId)
                    .then((response) => {
                        for (var i = 0; i < this.services.length; i++) {
                            if (this.services[i].id == id) {
                                this.services[i].offers = this.services[
                                    i
                                ].offers.filter(function (el) {
                                    return el.id != offerId;
                                });
                            }
                        }
                    });
                return;
            }

            if (this.application.kind == 'equipment') {
                axios
                    .delete("/api/v1/equipment-offers/" + offerId)
                    .then((response) => {
                        for (var i = 0; i < this.equipments.length; i++) {
                            if (this.equipments[i].id == id) {
                                this.equipments[i].offers = this.equipments[
                                    i
                                ].offers.filter(function (el) {
                                    return el.id != offerId;
                                });
                            }
                        }
                    });
            }
        },

        countSum(product) {
            return product.quantity * product.price;
        },

        handleFileUpload(offer, event) {
            this.file = event.target.files[0];

            let formData = new FormData();
            formData.append("file", this.file);
            formData.append("offer_id", offer.id);
            formData.append("kind", this.application.kind);

            axios
                .post("/upload-file", formData, {
                    headers: { "Content-Type": "multipart/form-data" },
                })
                .then((response) => {
                    offer.file = response.data.data.file;

                    this.snackbar.text = "Счет на оплату закреплен";
                    this.snackbar.status = true;
                })
                .catch((error) => {
                    console.error(error);
                });
        },

        getCompanies() {
            axios.get("/api/v1/companies").then((response) => {
                response.data.data.forEach((item) => {
                    this.companies.push(item);
                });

                // this.isLoading = false
            });
        },

        showAddCompanyDialog() {
            this.addCompanyDialog = true;
        },

        addCompany() {
            var data = { name: this.newCompany };
            axios.post("/api/v1/companies", data).then((response) => {
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
            if (this.application.kind == 'product') {
                this.priemka.product = item;
                this.priemka.name = item.product.name;
                this.priemka.unit = item.unit.name;
            }

            else if (this.application.kind == 'service') {
                this.priemka.service = item;
                this.priemka.name = item.service;
                this.priemka.unit = item.unit;
            }

            else if (this.application.kind == 'equipment') {
                this.priemka.equipment = item;
                this.priemka.name = item.equipment.name;
                this.priemka.unit = item.unit.name;
            }

            this.acceptProductDialog = true;
        },

        acceptInventory(inventoryId) {
            // accept application
            var data = {
                mode: "accept",
                kind: this.application.kind,
            };

            axios
                .put(
                    "/api/v1/inventory-applications/" +
                    inventoryId,
                    data
                )
                .then((response) => {
                    this.getApplication();

                    this.snackbar.text = "Приход товара/техники/услуги принят";
                    this.snackbar.status = true;

                    this.priemka = {
                        product: null,
                        equipment: null,
                        service: null,

                        name: null,
                        notes: null,
                        quantity: null,
                    };

                    this.acceptProductDialog = false;
                });
        },

        acceptProduct() {
            if (this.application.kind == 'product') {
                this.priemka.product.toBePrepared = this.priemka.quantity;

                // create inventory application
                var productData = {
                    product: this.priemka.product,
                    notes: this.priemka.notes,
                };

                axios
                    .put(
                        "/api/v1/application-products/" +
                        this.priemka.product.id +
                        "/prepare",
                        productData
                    )
                    .then((response) => {
                        this.priemka.product.prepared = response.data.prepared;
                        this.priemka.product.toBePrepared = null;

                        this.acceptInventory(response.data.inventory.id);
                    });
            }

            else if (this.application.kind == 'service') {
                this.priemka.service.toBePrepared = this.priemka.quantity;

                // create inventory application
                var data = {
                    service: this.priemka.service,
                    notes: this.priemka.notes,
                };

                axios
                    .put(
                        "/api/v1/application-services/" +
                        this.priemka.service.id +
                        "/prepare",
                        data
                    )
                    .then((response) => {
                        this.priemka.service.prepared = response.data.prepared;
                        this.priemka.service.toBePrepared = null;

                        this.acceptInventory(response.data.inventory.id);
                    });
            }

            else if (this.application.kind == 'equipment') {
                this.priemka.equipment.toBePrepared = this.priemka.quantity;

                // create inventory application
                var data = {
                    equipment: this.priemka.equipment,
                    notes: this.priemka.notes,
                };

                axios
                    .put(
                        "/api/v1/application-equipments/" +
                        this.priemka.equipment.id +
                        "/prepare",
                        data
                    )
                    .then((response) => {
                        this.priemka.equipment.prepared = response.data.prepared;
                        this.priemka.equipment.toBePrepared = null;

                        this.acceptInventory(response.data.inventory.id);
                    });
            }
        },

        showDeclineProduct(item) {
            // show decline product dialog
            this.declineProductDialog = true;
            this.declineInventoryApplicationId = item.id;

            console.log(this.declineInventoryApplicationId);
        },

        declineProduct() {
            var data = {
                mode: "decline",
                reason: this.declinedProductReason,
            };

            axios
                .put(
                    "/api/v1/inventory-applications/" +
                    this.declineInventoryApplicationId,
                    data
                )
                .then((response) => {
                    // this.getIncoming();

                    this.declineProductDialog = false;
                    this.declineInventoryApplicationId = null;
                    this.declinedProductReason = null;

                    this.snackbar.text = "Приход товара отклонен";
                    this.snackbar.status = true;

                    this.getApplication();
                });
        },

        // incoming(items) {
        //     return items.filter(el => el.status == 'waiting');
        // },

        offerHasChanged($event, offer) {
            // console.log('offer has changed: ' + offer.id);
            document.getElementById("offer_" + offer.id).style.visibility =
                "visible";
        },

        getCountNewApplications() {
            axios
                .get("/api/v1/badges-unread?type=applications")
                .then((response) => {
                    console.log("count of badges: ", response.data);

                    this.store.badgeNew = response.data;
                });
        },

        allCompleted(offers) {
            let res = true;

            for (let i = 0; i < offers.length; i++) {
                if (offers[i].status != 'completed') {
                    res = false;
                    break;
                }
            }

            return res;
        },

        showEquipmentNotes(item) {
            console.log(item);

            this.equipmentNotes = item.history;
            this.equipmentHistoryDialog = true;
        },

        getNotes() {
            // axios.get('/application')
        },

        getWorkingDays() {
            return parseInt(this.equipmentNotes.reduce((acc, n) => acc + n.hours, 0) / 8);
        },
    },

    watch: {
        "$route.query": {
            handler(newValue) {
                const { status } = newValue;

                console.log({ status });
            },
            immediate: true,
        },
    },
};
</script>

<style>
.multiselect--active {
    z-index: 1000;
}



.v-table__wrapper {
    overflow-x: auto;
    overflow-y: visible !important;
}


.v-table__wrapper table {
    overflow-x: auto;
    overflow-y: visible !important;
}


.v-expansion-panel-text__wrapper {
    padding-left: 0 !important;
}


.v-list-item-title {
    white-space: normal !important;
}


@media only screen and (min-width: 768px) {
    .oks-dialog {
        width: 500px;
    }


    .v-table__wrapper {
        overflow: visible !important;
    }

    .v-table__wrapper table {
        overflow: visible !important;
    }
}
</style>
